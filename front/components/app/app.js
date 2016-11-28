
import React from 'react'
import request from '../../scripts/lib/request'

import List from '../list/list'
import AppHeader from '../app-header/app-header'
import ClientView from '../client-view/client-view'

export default class App extends React.Component {

  constructor (props) {
    super(props)

    // initial state
    this.state = {
      clients: [],
      selectedClientId: null,
    }

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
    this.state.clients.sort((a, b) => a.updateTime > b.updateTime)

    // replace empty selection by first client
    if (this.state.selectedClientId === null) {
      this.state.selectedClientId = this.state.clients[0].id
    }

    // set state
    this.setState(this.state)
  }

  selectClient (clientId) {
    this.state.selectedClientId = clientId
    this.setState(this.state)
  }

  getSelectedClient () {
    if (this.state.selectedClientId !== null) {
      return this.findClient(this.state.selectedClientId)
    }
    return null
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
        onClick: evt => this.selectClient(client.id),
      }
    })

    let selectedClient = this.getSelectedClient()

    return (
      <div className="app">
        <div className="split-view">
          <div className="split-view__aside">
            <header className="split-view__header">
              <AppHeader />
            </header>
            <div className="split-view__master">
              <List items={clientItems} />
            </div>
          </div>
          <div className="split-view__detail">
            {selectedClient && <ClientView client={selectedClient} />}
          </div>
        </div>
      </div>
    )
  }
}
