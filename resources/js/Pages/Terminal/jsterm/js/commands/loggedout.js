import axios from 'axios'
import LoggedInCommands from './loggedin'

var COMMANDS = COMMANDS || {}

COMMANDS.clear = function (argv, cb) {
  this._terminal.div.innerHTML = ''
  cb()
}

COMMANDS.login = function (argv, cb) {
  this._terminal.returnHandler = function () {
    const password = this.stdout().innerHTML

    axios
      .post(route('web.terminal.login'), {
        password,
      })
      .then(() => {
        this.config.username = 'hydro4'
        this.loadCommands(LoggedInCommands)
        this.removeCommand('login')
        this.div.innerHTML = 'NanoTrasen Corporation Numbers Station #005\n\n'+
        'Last login: 2049-05-20 16:49:23\n'+
        'Use \'help\' for a list of commands\n\n'+
        'System uptime: 8488:11:12:22\n'+
        'Alerts: 1539 Critical Errors: 871\n\n'
      })
      .catch(() => {
        this.write('<br><span class="error">Wrong passphrase</span>')
      })
      .finally(() => {
        cb()
      })
  }.bind(this._terminal)
  this._terminal.write('Password: ')
  this._terminal.newStdout()
  this._terminal.scroll()
}

COMMANDS.bee = async function (argv, cb) {
  const file = await import(`../../files/bee.txt?raw`)
  this._terminal.write(file.default, 'file')
  cb()
}

export default COMMANDS
