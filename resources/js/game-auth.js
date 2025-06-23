import Echo from 'laravel-echo'
window.Echo = Echo

import Pusher from 'pusher-js'
window.Pusher = Pusher

function initEcho() {
  if (!window.EchoConfig) return
  window.Echo = new Echo(window.EchoConfig)

  const channel = `discord-login.${window.EchoPageState}`
  window.Echo.channel(channel).listen('.DiscordLogin', (e) => {
    window.Echo.leave(channel)

    const expires = new Date(e.cookie.expires * 1000).toUTCString()
    const cookie = `${e.cookie.name}=${e.cookie.value}; domain=${e.cookie.domain}; path=${e.cookie.path}; expires=${expires}; samesite=${e.cookie.samesite}`
    document.cookie = cookie

    window.parent.postMessage(
      {
        type: 'authenticated',
        user: {
          id: e.id,
          name: e.name,
          email: e.email,
          session: e.session,
        },
      },
      '*'
    )
  })
}

initEcho()
