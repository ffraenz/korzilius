
import React from 'react'

import List from '../list/list'
import ListItem from '../list-item/list-item'
import ChatView from '../chat-view/chat-view'
import Scrollable from '../scrollable/scrollable'

const ClientView = props => {
  let client = props.client
  let title = client.company
    ? client.company : client.firstname + ' ' + client.lastname

  let detailItems = composeDetailItems(client)

  return (
    <div className="client-view">
      <div className="split-view split-view--reversed split-view--secondary">
        <div className="split-view__aside">
          <header className="split-view__header">
            <div className="client-view__header">
              <h2 className="client-view__headline">{title}</h2>
            </div>
          </header>
          <div className="split-view__master">
            <Scrollable>
              <List modifiers={['compact']} items={detailItems} />
            </Scrollable>
          </div>
        </div>
        <div className="split-view__detail">
          <ChatView
            context={`client-${client.id}`}
            messages={props.messages}
            infiniteScrolling={!client.reachedMessagesEnd}
            onMessagesEndReached={() => {
              props.onMessagesEndReached(client)
            }} />
        </div>
      </div>
    </div>
  )
}

function composeDetailItems (client) {
  // compose private section
  let privateSection = []

  privateSection.push({
    type: 'section',
    title: 'Privat',
  })

  if (client.company !== null) {

    // add the company's contact person
    if (client.firstname !== null) {
      privateSection.push({
        text: client.firstname + ' ' + client.lastname,
        icon: 'user',
        href: '#',
      })
    }
  }

  if (client.street || client.location) {
    let streetParts = []

    if (client.street !== null) {
      streetParts.push(client.street)
    }

    if (client.houseNumber !== null) {
      streetParts.push(client.houseNumber)
    }

    let locationParts = []

    if (client.location !== null) {
      locationParts.push(client.location)
    }

    if (client.postCode !== null) {
      locationParts.push(client.postCode)
    }

    let address =
      (streetParts.length > 0 ? streetParts.join(', ') + '\n' : '') +
      (locationParts.length > 0 ? locationParts.join(' ') : '')

    privateSection.push({
      text: address,
      icon: 'home',
      href: '#',
    })
  }

  if (client.emailPrivate) {
    privateSection.push({
      text: client.emailPrivate,
      icon: 'envelope',
      href: 'mailto:' + client.emailPrivate,
    })
  }

  if (client.mobilePrivate) {
    privateSection.push({
      text: client.mobilePrivate,
      icon: 'phone',
      href: '#',
    })
  }

  if (client.phonePrivate) {
    privateSection.push({
      text: client.phonePrivate,
      icon: 'phone',
      href: '#',
    })
  }

  if (client.fax) {
    privateSection.push({
      text: client.fax,
      icon: 'fax',
      href: '#',
    })
  }

  if (client.birthdate) {
    let date = new Date(client.birthdate * 1000)
    privateSection.push({
      text:
        ('00' + date.getDate()).substr(-2) + '.' +
        ('00' + (date.getMonth() + 1)).substr(-2) + '.' +
        date.getFullYear(),
      icon: 'birthday-cake',
      href: '#',
    })
  }

  if (client.laluxClientId) {
    privateSection.push({
      text: client.laluxClientId,
      icon: 'building',
      href: '#',
    })
  }

  // compose pro section
  let proSection = []

  proSection.push({
    type: 'section',
    title: 'Professionel',
  })

  if (client.emailPro) {
    proSection.push({
      text: client.emailPro,
      icon: 'envelope',
      href: 'mailto:' + client.emailPro,
    })
  }

  if (client.mobilePro) {
    proSection.push({
      text: client.mobilePro,
      icon: 'phone',
      href: '#',
    })
  }

  if (client.phonePro) {
    proSection.push({
      text: client.phonePro,
      icon: 'phone',
      href: '#',
    })
  }

  // compose form section
  let formSection = []

  formSection.push({
    type: 'section',
    title: 'Formularer',
  })

  formSection.push({
    text: 'Protocole d\'entrevue',
    icon: 'file-text-o',
    href: '#'
  })

  formSection.push({
    text: 'Note au département',
    icon: 'file-text-o',
    href: '#'
  })

  formSection.push({
    text: 'Estimation',
    icon: 'file-text-o',
    href: '#'
  })

  formSection.push({
    text: 'Inventaire avant-projet',
    icon: 'file-text-o',
    href: '#'
  })

  formSection.push({
    text: 'Ventilation Vehi. Auto. Agricoles',
    icon: 'file-text-o',
    href: '#'
  })

  // merge sections if they have any items
  return [].concat(
    privateSection.length > 1 ? privateSection : [],
    proSection.length > 1 ? proSection : [],
    formSection.length > 1 ? formSection : [])
}

export default ClientView
