// Copyright 2013 Clark DuVall
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

import axios from 'axios'

var COMMANDS = COMMANDS || {}

COMMANDS.cat = async function (argv, cb) {
  var filenames = this._terminal.parseArgs(argv).filenames,
    stdout

  this._terminal.scroll()
  if (!filenames.length) {
    this._terminal.returnHandler = function () {
      stdout = this.stdout()
      if (!stdout) return
      stdout.innerHTML += '<br>' + stdout.innerHTML + '<br>'
      this.scroll()
      this.newStdout()
    }.bind(this._terminal)
    return
  }
  for (const i in filenames) {
    const filename = filenames[i]
    var entry = this._terminal.getEntry(filename)

    if (!entry) this._terminal.write('cat: ' + filename + ': No such file or directory')
    else if (entry.type === 'dir') this._terminal.write('cat: ' + filename + ': Is a directory.')
    else if (entry.type === 'textFile') {
      const file = await import(`../../files/${entry.contents}.txt?raw`)
      this._terminal.write(file.default, 'file')
    } else this._terminal.write(entry.contents, 'file')

    if (i !== filenames.length - 1) this._terminal.write('<br>')
  }
  cb()
}

COMMANDS.cd = function (argv, cb) {
  var filename = this._terminal.parseArgs(argv).filenames[0],
    entry

  if (!filename) filename = '/'
  entry = this._terminal.getEntry(filename)
  if (!entry) this._terminal.write('bash: cd: ' + filename + ': No such file or directory')
  else if (entry.type !== 'dir')
    this._terminal.write('bash: cd: ' + filename + ': Not a directory.')
  else this._terminal.cwd = entry
  cb()
}

COMMANDS.ls = function (argv, cb) {
  var result = this._terminal.parseArgs(argv),
    args = result.args,
    filename = result.filenames[0],
    entry = filename ? this._terminal.getEntry(filename) : this._terminal.cwd,
    maxLen = 0,
    writeEntry

  writeEntry = function (e, str) {
    this.writeLink(e, str)
    if (args.indexOf('l') > -1) {
      if ('description' in e) this.write(' - ' + e.description)
      this.write('<br>')
    } else {
      // Make all entries the same width like real ls. End with a normal
      // space so the line breaks only after entries.
      this.write(Array(maxLen - e.name.length + 2).join('&nbsp') + ' ')
    }
  }.bind(this._terminal)

  if (!entry) this._terminal.write('ls: cannot access ' + filename + ': No such file or directory')
  else if (entry.type === 'dir') {
    var dirStr = this._terminal.dirString(entry)
    maxLen = entry.contents.reduce(function (prev, cur) {
      return Math.max(prev, cur.name.length)
    }, 0)

    for (var i in entry.contents) {
      var e = entry.contents[i]
      if (args.indexOf('a') > -1 || e.name[0] !== '.') writeEntry(e, dirStr + '/' + e.name)
    }
  } else {
    maxLen = entry.name.length
    writeEntry(entry, filename)
  }
  cb()
}

COMMANDS.gimp = function (argv, cb) {
  var filename = this._terminal.parseArgs(argv).filenames[0],
    entry,
    imgs

  if (!filename) {
    this._terminal.write('gimp: please specify an image file.')
    cb()
    return
  }

  entry = this._terminal.getEntry(filename)
  if (!entry || entry.type !== 'img') {
    this._terminal.write('gimp: file ' + filename + ' is not an image file.')
  } else {
    let importPath
    if (entry.imageType === 'gif') {
      importPath = `../../images/${entry.contents}.gif`
    } else if (entry.imageType === 'png') {
      importPath = `../../images/${entry.contents}.png`
    }

    const imageUrl = new URL(importPath, import.meta.url).href
    this._terminal.write('<img src="' + imageUrl + '"/>')
    imgs = this._terminal.div.getElementsByTagName('img')
    imgs[imgs.length - 1].onload = function () {
      this.scroll()
    }.bind(this._terminal)
    if ('caption' in entry) this._terminal.write('<br/>' + entry.caption)
  }
  cb()
}

COMMANDS.sudo = function (argv, cb) {
  var count = 1
  this._terminal.returnHandler = function () {
    const password = this.stdout().innerHTML
    if (count <= 3) {
      console.log(password)
      axios
        .post(route('web.terminal.sudo'), {
          password,
        })
        .then(() => {
          this.config.sudoer = true
          this._execute(argv.join(' '), true)
          cb()
        })
        .catch(() => {
          count++
          if (count <= 3) {
            this.write('<br/>Sorry, try again.<br/>')
            this.write('[sudo] password for ' + this.config.username + ': ')
            this.newStdout()
            this.scroll()
          } else {
            this.write('<br/>sudo: 3 incorrect password attempts')
            cb()
          }
        })
    } else {
      count++
      this.write('<br/>sudo: 3 incorrect password attempts')
      cb()
    }
  }.bind(this._terminal)
  if (this._terminal.config.sudoer) {
    this._terminal._execute(argv.join(' '), true)
    cb()
  } else {
    this._terminal.write('[sudo] password for ' + this._terminal.config.username + ': ')
    this._terminal.newStdout()
    this._terminal.scroll()
  }
}

COMMANDS.tree = function (argv, cb) {
  var term = this._terminal,
    home

  function writeTree(dir, level) {
    dir.contents.forEach(function (entry) {
      var str = ''

      if (entry.name.startsWith('.')) return
      for (var i = 0; i < level; i++) str += '|  '
      str += '|&mdash;&mdash;'
      term.write(str)
      term.writeLink(entry, term.dirString(dir) + '/' + entry.name)
      term.write('<br>')
      if (entry.type === 'dir') writeTree(entry, level + 1)
    })
  }
  home = this._terminal.getEntry('/')
  this._terminal.writeLink(home, '/')
  this._terminal.write('<br>')
  writeTree(home, 0)
  cb()
}

COMMANDS.help = function (argv, cb) {
  // this._terminal.write(
  //   'You can navigate either by clicking on anything that ' +
  //     '<a href="javascript:void(0)">underlines</a> when you put your mouse ' +
  //     'over it, or by typing commands in the terminal. Type the name of a ' +
  //     '<span class="exec">link</span> to view it. Use "cd" to change into a ' +
  //     '<span class="dir">directory</span>, or use "ls" to list the contents ' +
  //     'of that directory. The contents of a <span class="text">file</span> ' +
  //     'can be viewed using "cat". <span class="img">Images</span> are ' +
  //     'displayed using "gimp".<br><br>If there is a command you want to get ' +
  //     'out of, press Ctrl+C or Ctrl+D.<br><br>'
  // )
  this._terminal.write('Available commands:<br>')
  for (var c in this._terminal.commands) {
    if (this._terminal.commands.hasOwnProperty(c) && !c.startsWith('_'))
      this._terminal.write(c + '  ')
  }
  cb()
}

COMMANDS.lpr = function (argv, cb) {
  const fileName = this._terminal.parseArgs(argv).filenames[0]
  if (!fileName) {
    this._terminal.write('lpr: please specify a file')
    return cb()
  }

  axios
    .post(route('web.terminal.print'), { fileName })
    .then((res) => {
      const server = res.data.message
      this._terminal.write('lpr: file sent to to qqqqqqueeeeeeu---<br>')
      setTimeout(() => {
        this._terminal.write('.<br>')
        setTimeout(() => {
          this._terminal.write('..<br>')
          setTimeout(() => {
            this._terminal.write('...<br>')
            setTimeout(() => {
              this._terminal.write('lpr: destination host unknown<br>')
              this._terminal.write(`lpr: sending file to %$$##*${server}- server<br><br>`)
              this._terminal.write(
                'O26+M9i2iaPeSTMDHTgSg+tHgjW58+sqUpP6mSyqzpUPFmLGDiNjW9d//8Z0j9gjH35OPXU2XgRcjy1dYzhicTnP2D5er3OZHfQlkxUS5tY=<br><br>'
              )
              cb()
            }, 1000)
          }, 1000)
        }, 1000)
      }, 1000)
    })
    .catch((res) => {
      this._terminal.write(`lpr: ${res.response.data.message}`)
      cb()
    })
}

export default COMMANDS
