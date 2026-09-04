<?php

namespace App\Libraries\GameBuilder;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Support\Facades\File;
use Str;
use Symfony\Component\Process\Process;

class Repo
{
    private $build;

    private $repoUrl = 'github.com';

    private $userName = 'robuddybot';

    private $userEmail = 'robuddybot@goonhub.com';

    private $repoShared;

    public $repoDir;

    public function __construct(Build $build, string $serverDir)
    {
        $this->build = $build;
        $this->repoShared = storage_path('app/game-builder/shared-git');
        $this->repoDir = "$serverDir/repo";

        $org = config('goonhub.github_organization');
        $repo = config('goonhub.github_repo');
        $this->repoUrl = "github.com/$org/$repo";

        if (File::missing($this->repoShared)) {
            File::makeDirectory($this->repoShared);
        }
    }

    private function run(array $cmd, string $cwd = '', int $timeout = 60, array $env = [])
    {
        $this->build->checkCancelled();
        if (! $cwd) {
            $cwd = $this->repoDir;
        }

        $process = new Process($cmd, $cwd, timeout: $timeout, env: $env);
        $this->build->runProcess($process, logOut: false);

        return trim($process->getOutput(), " \n\r\t\v\0\"");
    }

    private function getRepoUrl(string $url)
    {
        if (! str_starts_with($url, 'http')) {
            $url = "https://$url";
        }
        if (str_ends_with($url, '.git')) {
            $url = substr($url, 0, -4);
        }
        $urlParts = parse_url($url);
        $githubToken = GitHub::getConnectionConfig()['token'];
        $remoteUrl = "{$urlParts['host']}{$urlParts['path']}";
        $remoteUrlWithAuth = "{$this->userName}:$githubToken@$remoteUrl";

        return (object) [
            'base' => $remoteUrl,
            'full' => "https://$remoteUrlWithAuth",
            'slug' => Str::slug($remoteUrl),
        ];
    }

    private function updateReference(object $repoUrl)
    {
        $refDir = "{$this->repoShared}/{$repoUrl->slug}";
        if (File::exists($refDir)) {
            $this->run(['git', 'fetch', '--all'], cwd: $refDir);

        } else {
            $this->build->log('Cloning shared repo, this might take a while', flush: true);
            $this->run([
                'git', 'clone', '--bare',
                '-c', "user.name={$this->userName}",
                '-c', "user.email={$this->userEmail}",
                $repoUrl->full, $repoUrl->slug,
            ], cwd: $this->repoShared, timeout: 300);
        }
    }

    public function init()
    {
        $lockFile = "{$this->repoDir}/.git/index.lock";
        if (File::exists($lockFile)) {
            File::delete($lockFile);
        }

        $repoUrl = $this->getRepoUrl($this->repoUrl);
        $this->updateReference($repoUrl);

        if (File::missing($this->repoDir)) {
            $this->build->log('Cloning new repo');
            File::makeDirectory($this->repoDir, recursive: true);

            $this->run([
                'git', 'clone',
                '-c', "user.name={$this->userName}",
                '-c', "user.email={$this->userEmail}",
                '--reference', "{$this->repoShared}/{$repoUrl->slug}",
                $repoUrl->full, $this->repoDir,
            ], timeout: 300);
        }

        $this->run(
            ['tools/bootstrap/python', '-m', 'hooks.install'],
            timeout: 60,
            env: ['HOOKS_INCLUDE_TGUI' => '1', 'HOOKS_INCLUDE_BASE' => '1']
        );

        $process = Process::fromShellCommandline(
            'git config --file .gitmodules --get-regexp path | awk \'{ print $1 }\'',
            $this->repoDir
        );
        $process->mustRun();
        $submodules = explode("\n", $process->getOutput());
        foreach ($submodules as $submodule) {
            if (empty($submodule)) {
                continue;
            }

            preg_match('/submodule\.(.*?)\./i', $submodule, $matches);
            $key = $matches[1];
            $path = $this->run([
                'git', 'config', '--file', '.gitmodules',
                '--get', "submodule.$key.path",
            ]);
            $url = $this->run([
                'git', 'config', '--file', '.gitmodules',
                '--get', "submodule.$key.url",
            ]);

            $subRepoUrl = $this->getRepoUrl($url);
            $this->updateReference($subRepoUrl);

            if (File::isEmptyDirectory("{$this->repoDir}/$path")) {
                $this->run(['git', 'submodule', 'init',  '--', $path]);
                $this->run(['git', 'submodule', 'set-url', '--', $path, "{$subRepoUrl->full}.git"]);
                $this->run([
                    'git', 'submodule', 'update',
                    '--reference', "{$this->repoShared}/{$subRepoUrl->slug}",
                    '--', $path,
                ], timeout: 300);
            }
        }
    }

    public function fetch()
    {
        return $this->run([
            'git', 'fetch', '--recurse-submodules', 'origin',
        ], timeout: 300);
    }

    public function reset()
    {
        return $this->run(['git', 'reset', '--recurse-submodules', '--hard', '@{u}']);
    }

    public function getHash(?string $branch = null): string
    {
        return $this->run(['git', 'rev-parse', $branch ? $branch : '@']);
    }

    public function getAuthor(string $commit)
    {
        return $this->run(['git', 'log', '--format="%an"', '-n', '1', $commit]);
    }

    public function checkout(string $branch)
    {
        return $this->run(['git', 'checkout', '--recurse-submodules', $branch]);
    }

    public function checkoutRemote(string $branch)
    {
        return $this->run(['git', 'checkout', '--recurse-submodules', '-f', '-B', $branch, '--track', "origin/$branch"]);
    }

    public function createAndCheckoutBranch(string $branch)
    {
        return $this->run(['git', 'checkout', '--recurse-submodules', '-b', $branch]);
    }

    public function fetchPr(int $prId)
    {
        return $this->run(['git', 'fetch', 'origin', "pull/{$prId}/head:pr-{$prId}"]);
    }

    public function resetBranchToCommit(string $commit)
    {
        return $this->run(['git', 'reset', '--hard', $commit]);
    }

    public function merge(string $branch)
    {
        return $this->run(['git', 'merge', '--no-commit', '--no-ff', $branch]);
    }

    public function getConflictedFiles()
    {
        return $this->run(['git', 'diff', '--name-only', '--diff-filter=U', '--relative']);
    }

    public function abortMerge()
    {
        return $this->run(['git', 'merge', '--abort']);
    }

    public function commit(string $message)
    {
        return $this->run(['git', 'commit', '--no-gpg-sign', '-m', $message]);
    }

    public function getMessage(string $commit)
    {
        return $this->run(['git', 'log', '--format="%B"', '-n', 1, $commit]);
    }
}
