
import React from 'react'
import Message from '../message/message'

const MessageThread = props => {
  let messages = props.messages
  let isOtherSide = (messages[0].senderClientId !== null)

  // compose class names
  let className = ['message-thread'].concat(
    props.classes,
    props.modifiers.map(modifier => 'message-thread--' + modifier),
    isOtherSide ? 'message-thread--side-other' : []
  ).join(' ')

  // retrieve thread meta data from first message
  let message = messages[0]

  let avatarImageUrl = null
  if (!isOtherSide && message.senderUser !== null) {
    avatarImageUrl = message.senderUser.avatarImageUrl
  }

  return (
    <div className={className} key={message.id}>
      <div className="message-thread__source">
        <div className="message-thread__avatar">
          {avatarImageUrl &&
            <img className="message-thread__avatar-img" src={avatarImageUrl} />}
        </div>
      </div>
      <div className="message-thread__messages">
        {messages.map(message => <Message message={message} />)}
      </div>
    </div>
  )
}

MessageThread.defaultProps = {
  classes: [],
  modifiers: [],
  side: 'self'
}

export default MessageThread
