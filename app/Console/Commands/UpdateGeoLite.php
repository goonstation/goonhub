<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateGeoLite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:update-geo-lite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the GeoLite database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $version = config('goonhub.geoip_update_version');
        $homePath = getenv('HOME');
        $binPath = "$homePath/geoipupdate_{$version}";

        if (! is_dir($binPath)) {
            $fileName = "geoipupdate_{$version}_linux_amd64";
            $fileNameWithExt = "$fileName.tar.gz";
            $downloadUrl = "https://github.com/maxmind/geoipupdate/releases/download/v$version/$fileNameWithExt";
            copy($downloadUrl, "$homePath/$fileNameWithExt");
            exec("tar -xzf $homePath/$fileNameWithExt --one-top-level=$homePath");
            rename("$homePath/$fileName", $binPath);
            unlink("$homePath/$fileNameWithExt");
        }

        $configFile = storage_path('app').'/GeoIP.conf';
        $outputDir = storage_path('app').'/GeoLite2';
        $lockFile = storage_path('app').'/.geoipupdate.lock';
        $accountId = config('goonhub.maxmind_account_id');
        $licenseKey = config('goonhub.maxmind_license_key');
        file_put_contents($configFile,
            "AccountID $accountId\n".
            "LicenseKey $licenseKey\n".
            "EditionIDs GeoLite2-ASN GeoLite2-City GeoLite2-Country\n".
            "DatabaseDirectory $outputDir\n".
            "LockFile $lockFile"
        );

        if (! is_dir($outputDir)) {
            mkdir($outputDir);
        }
        exec("$binPath/geoipupdate -f $configFile -v");

        return Command::SUCCESS;
    }
}
