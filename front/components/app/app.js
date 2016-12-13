
import React from 'react'
import request from '../../scripts/lib/request'
import socketIO from 'socket-io'

import List from '../list/list'
import AppHeader from '../app-header/app-header'
import ClientView from '../client-view/client-view'
import SearchField from '../search-field/search-field'
import Scrollable from '../scrollable/scrollable'

export default class App extends React.Component {

  constructor (props) {
    super(props)

    // initial state
    this.state = {
      clients: [],
      clientMessages: {},
      selectedClientId: null,
    }

    // connect to socket server
    this.socketServer = socketIO('http://localhost:50080')

    this.socketServer.on('clientUpdated', client => {
      this.integrateClients([client])
    })

    this.socketServer.on('messageReceived', message => {
      this.integrateMessages([message])
      console.log('message received', message)
    })

    // request last updated clients
    request('/api/clients').then(response => {
      let clients = response.data
      this.integrateClients(clients)
    })
  }

  findClient (id) {
    return this.state.clients.find(client => {
      return client.id === id
    })
  }

  integrateClients (clients) {
    // integrate each client in local state
    clients.forEach(client => {
      // check if client already exists
      let existingClient = this.findClient(client)
      if (existingClient !== undefined) {
        // replace client with new one
        let index = this.state.clients.indexOf(existingClient)
        this.state.clients.splice(index, 1, client)
      } else {
        // append client to local state
        this.state.clients.push(client)
      }
    })

    // sort clients by update time
    this.state.clients.sort((a, b) => b.activeTime - a.activeTime)

    // set state
    this.setState(this.state)

    // replace empty selection by first client
    if (this.state.selectedClientId === null) {
      this.selectClient(this.state.clients[0])
    }
  }

  integrateMessages (messages) {
    messages.forEach(message => {
      // match client to message
      let clientId = message.senderClientId || message.receiverClientId

      if (clientId) {
        if (this.state.clientMessages[clientId] === undefined) {
          this.state.clientMessages[clientId] = [message]
        } else if (this.state.clientMessages[clientId].length === 0) {
          this.state.clientMessages[clientId].push(message)
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

      // set state
      this.setState(this.state)
    });
  }

  selectClient (client) {
    this.state.selectedClientId = client.id
    this.setState(this.state)

    if (this.state.clientMessages[client.id] === undefined) {
      // request messages for this client for the first time
      this.state.clientMessages[client.id] = []
      request(`/api/clients/${client.id}/messages`).then(response => {
        let messages = response.data
        this.integrateMessages(messages)
      })
    }
  }

  getSelectedClient () {
    if (this.state.selectedClientId !== null) {
      return this.findClient(this.state.selectedClientId)
    }
    return null
  }

  getMessagesForClient (client) {
    let messages = this.state.clientMessages[client.id]
    if (messages !== undefined) {
      return messages
    }
    return []
  }

  render () {
    let clientItems = this.state.clients.map(client => {
      let title = client.company
        ? client.company : client.firstname + ' ' + client.lastname

      let detail = client.location
      let active = (this.state.selectedClientId === client.id)

      return {
        title: title,
        text: detail,
        modifiers: active ? ['active'] : [],
        onClick: evt => this.selectClient(client),
      }
    })

    let clientView = null
    let selectedClient = this.getSelectedClient()

    if (selectedClient) {
      let messages = this.getMessagesForClient(selectedClient)
      clientView = <ClientView client={selectedClient} messages={messages} />
    }

    return (
      <div className="app">
        <div className="split-view">
          <div className="split-view__aside">
            <header className="split-view__header">
              <AppHeader />
              <SearchField />
            </header>
            <div className="split-view__master">
              <Scrollable>
                <List items={clientItems} />
              </Scrollable>
            </div>
          </div>
          <div className="split-view__detail">
            {clientView}
          </div>
        </div>
      </div>
    )
  }
}
