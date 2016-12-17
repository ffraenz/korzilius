
import React from 'react'
import moment from 'moment'

import MessageThread from '../message-thread/message-thread'
import Scrollable from '../scrollable/scrollable'

export default class ChatView extends React.Component {

  static get defaultProps() {
    return {
      context: null
    }
  }

  render () {
    let messageThreads = this.composeMessageThreads(this.props.messages)

    return (
      <div className="chat-view">
        <div className="chat-view__content">
          <Scrollable
            flow="up"
            infiniteScrolling={this.props.infiniteScrolling}
            onScrollEndReached={this.props.onMessagesEndReached}
            context={this.props.context}>
            <div className="chat-view__tape">
              {messageThreads}
            </div>
          </Scrollable>
        </div>
        <div className="chat-view__compose">
        </div>
      </div>
    )
  }

  composeMessageThreads (messages) {
    // group messages into threads of messages
    let threadMessages = []
    messages.forEach((message, index) => {
      // compare this message to the last one
      let lastMessage = (index > 0 ? messages[index - 1] : null)

      // determine if this message should be added into a new thread
      let newThread = (lastMessage === null || (
        // create a new thread when 20 min elapsed between messages
        lastMessage.sendTime - message.sendTime > 60 * 20 ||
        // create a new thread when the sender or receiver changed
        lastMessage.receiverClientId !== message.receiverClientId ||
        lastMessage.receiverUserId !== message.receiverUserId ||
        lastMessage.senderClientId !== message.senderClientId ||
        lastMessage.senderUserId !== message.senderUserId
      ))

      if (newThread) {
        // open new thread
        threadMessages.unshift([])
      }

      // push message to current thread
      threadMessages[0].unshift(message)
    })

    // compose content
    let content = []
    threadMessages.forEach((messages, index) => {
      // send time of thread
      let sendMoment = moment.unix(messages[0].sendTime)

      if (index === 0 || messages[0].sendTime - threadMessages[index - 1][0].sendTime > 60 * 20) {

        // format time
        let formattedSendTime
        if (sendMoment.isSame(new Date(), 'day')) {
          formattedSendTime = sendMoment.format('LT')
        } else if (sendMoment.isSame(new Date(), 'year')) {
          formattedSendTime = sendMoment.format('Do MMMM LT')
        } else {
          formattedSendTime = sendMoment.format('Do MMMM YYYY')
        }

        content.push(
          <div className="chat-view__section">
            {formattedSendTime}
          </div>
        )
      }

      content.push(
        <MessageThread messages={messages} />
      )
    })

    return content
  }
}
