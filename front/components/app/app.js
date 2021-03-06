
import React from 'react'
import request from '../../scripts/lib/request'

import List from '../list/list'
import AppHeader from '../app-header/app-header'
import ClientView from '../client-view/client-view'
import SearchField from '../search-field/search-field'
import Scrollable from '../scrollable/scrollable'
import Modal from '../modal/modal'

const CLIENTS_PAGE_COUNT = 20
const CLIENT_MESSAGES_PAGE_COUNT = 30

export default class App extends React.Component {

  constructor (props) {
    super(props)

    // initial state
    this.state = {
      client: null,
      clients: [],
      searching: false,
      clientSearchResults: null,
      loadingClients: false,
      reachedClientsEnd: false,
      clientMessages: {},
      lastClientsPageActiveTime: null,
    }

    // check for client in url
    let matches = location.pathname.match(/^\/clients\/([0-9]+)\/?$/)
    if (matches) {
      // request selected client
      let clientId = parseInt(matches[1])
      this.fetchClientById(clientId).then(client => {
        this.selectClient(client)

        // request first clients page
        this.fetchNextClientsPage()
      })
    } else {
      // request first clients page
      this.fetchNextClientsPage()
    }
  }

  findClient (id) {
    return this.state.clients.find(client => client.id === id)
  }

  fetchNextClientsPage () {
    if (this.state.loadingClients || this.state.reachedClientsEnd) {
      // nothing to load
      return
    }

    this.setState({
      loadingClients: true
    })

    // retrieve last active client
    let clients = this.state.clients
    let data = {}

    if (this.state.lastClientsPageActiveTime !== null) {
      data['active_before'] = this.state.lastClientsPageActiveTime
    }

    // request next clients page
    request('/api/clients', { data }).then(response => {
      let clients = response.data

      if (clients.length < CLIENTS_PAGE_COUNT) {
        this.state.reachedClientsEnd = true
      }

      this.integrateClients(clients)
      this.state.loadingClients = false

      this.state.lastClientsPageActiveTime =
        clients.reduce((activeTime, client) => {
          return !activeTime
            ? client.activeTime
            : Math.min(client.activeTime, activeTime)
        })
    })
  }

  fetchClientById (clientId) {
    return request(`/api/clients/${clientId}`)
      .then(response => {
        let client = response.data
        let integratedClients = this.integrateClients([client])
        return integratedClients[0]
      })
  }

  integrateClients (clients) {
    let integratedClients = []

    // integrate each client in local state
    clients.forEach(client => {
      // set flags on client
      client.loadingMessages = false
      client.reachedMessagesEnd = false

      // check if client already exists
      let existingClient = this.findClient(client.id)
      if (existingClient !== undefined) {
        // update client
        if (existingClient.updateTime < client.updateTime) {
          for (let key in client) {
            existingClient[key] = client[key]
          }
        }

        integratedClients.push(existingClient)
      } else {
        // append client to local state
        this.state.clients.push(client)
        integratedClients.push(client)
      }
    })

    // sort clients by update time
    this.state.clients.sort((a, b) => b.activeTime - a.activeTime)

    // replace empty selection by first client
    if (this.state.client === null) {
      this.selectClient(this.state.clients[0])
    }

    this.setState(this.state)
    return integratedClients
  }

  fetchNextClientMessagesPage (client) {
    if (client.loadingMessages || client.reachedMessagesEnd) {
      // nothing to load
      return
    }

    client.loadingMessages = true
    this.setState(this.state)

    // retrieve oldest message from this client
    let clientMessages = this.state.clientMessages[client.id]
    let data = {}

    if (clientMessages) {
      data['sent_before'] = clientMessages[clientMessages.length - 1].sendTime
    }

    // request next client messages page
    request(`/api/clients/${client.id}/messages`, { data }).then(response => {
      let messages = response.data

      if (messages.length < CLIENT_MESSAGES_PAGE_COUNT) {
        client.reachedMessagesEnd = true
      }

      this.integrateClientMessages(messages)
      client.loadingMessages = false
      this.setState(this.state)
    })
  }

  integrateClientMessages (messages) {
    messages.forEach(message => {
      // match client to message
      let clientId = message.senderClientId || message.receiverClientId

      if (clientId) {
        if (this.state.clientMessages[clientId] === undefined) {
          this.state.clientMessages[clientId] = [message]
        } else {
          let clientMessages = this.state.clientMessages[clientId]
          let existingMessage = clientMessages.find(m => m.id === message.id)

          if (existingMessage) {
            // replace existing message if newer
            if (message.updateTime > existingMessage.updateTime) {
              let index = clientMessages.indexOf(existingMessage)
              clientMessages.splice(index, 1, message)
            }
          } else {
            let oldestMessage = clientMessages[clientMessages.length - 1]
            if (message.sendTime < oldestMessage.sendTime) {
              // append to end
              clientMessages.push(message)
            } else {
              // integrate message between others
              let index = clientMessages.length - 1
              while (
                index > 0 &&
                message.sendTime > clientMessages[index].sendTime
              ) {
                index -= 1
              }

              clientMessages.splice(index, 0, message)
            }
          }
        }
      }
    });

    this.setState(this.state)
  }

  selectClient (client) {
    if (this.state.client === client) {
      // nothing to do
      return
    }

    this.state.client = client
    this.setState(this.state)

    // update url
    history.replaceState(null, null, `/clients/${client.id}`);

    if (this.state.clientMessages[client.id] === undefined) {
      // request messages for this client for the first time
      this.fetchNextClientMessagesPage(client)
    }
  }

  selectNextClient (forward = true) {
    // choose client list
    let clients = !this.state.searching
      ? this.state.clients
      : this.state.clientSearchResults

    let index = clients.indexOf(this.state.client)
    if (index === -1) {
      // select first
      index = 0
    } else {
      // move selection
      if (forward) {
        index = Math.min(index + 1, clients.length - 1)
      } else {
        index = Math.max(index - 1, 0)
      }
    }

    // select client
    let client = clients[index]
    this.selectClient(client)
    return client
  }

  getMessagesForClient (client) {
    let messages = this.state.clientMessages[client.id]
    if (messages !== undefined) {
      return messages
    }
    return []
  }

  postMessageToClient (client, channel, text) {
    request(`/api/clients/${client.id}/messages`, {
      method: 'POST',
      data: {
        channel,
        text
      }
    }).then(response => {
      console.log(response)
    })
  }

  onStartSearching () {
    // empty search results
    this.setState({
      searching: true
    })
  }

  onFinishSearching () {
    // clear search results
    this.setState({
      searching: false,
      clientSearchResults: null
    })
  }

  onSearch (keywords) {
    if (!keywords) {
      return;
    }
    request(`/api/clients`, {
      data: {
        q: keywords
      }
    }).then(response => {
      if (!this.state.searching) {
        return
      }

      let clients = this.integrateClients(response.data)
      this.setState({ clientSearchResults: clients })
    }).catch(() => {
      this.setState({ clientSearchResults: [] })
    })
  }

  render () {
    let clients = this.state.clients
    if (this.state.searching) {
      clients = this.state.clientSearchResults !== null
        ? this.state.clientSearchResults
        : []
    }

    let clientItems = clients.map(client => {
      let title = client.company
        ? client.company : client.firstname + ' ' + client.lastname

      let detail = client.location
      let active = (this.state.client.id === client.id)

      return {
        title: title,
        text: detail,
        key: client.id,
        modifiers: active ? ['active'] : [],
        onClick: evt => this.selectClient(client),
      }
    })

    let clientView = null
    let client = this.state.client

    if (client) {
      let messages = this.getMessagesForClient(client)
      clientView = (
        <ClientView
          client={client}
          messages={messages}
          onModalChange={modal => this.setState({ modal })}
          onMessagePost={(channel, text) =>
            this.postMessageToClient(client, channel, text)}
          onMessagesEndReached={this.fetchNextClientMessagesPage.bind(this)} />
      )
    }

    return (
      <div className="app">
        <div className="split-view">
          <div className="split-view__aside">
            <header className="split-view__header">
              <AppHeader />
              <SearchField
                onStart={this.onStartSearching.bind(this)}
                onFinish={this.onFinishSearching.bind(this)}
                onSearch={this.onSearch.bind(this)}
                onSelectPreviousResult={() => this.selectNextClient(false)}
                onSelectNextResult={() => this.selectNextClient(true)} />
            </header>
            <div className="split-view__master">
              <Scrollable
                infiniteScrolling={
                  !this.state.searching && !this.state.reachedClientsEnd}
                onScrollEndReached={this.fetchNextClientsPage.bind(this)}>
                <List items={clientItems} />
              </Scrollable>
            </div>
          </div>
          <div className="split-view__detail">
            {clientView}
          </div>
        </div>
        { this.state.modal
          ? React.createElement(Modal, Object.assign({
              onDismiss: () => this.setState({ modal: null }),
              onSubmit: () => this.setState({ modal: null })
            }, this.state.modal))
          : null }
      </div>
    )
  }
}
