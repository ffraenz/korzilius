
import React from 'react'

const FLOW_DOWN = 'down';
const FLOW_UP = 'up';

export default class Scrollable extends React.Component {

  static get defaultProps() {
    return {
      flow: FLOW_DOWN,
      classes: [],
      modifiers: [],
    }
  }

  constructor (props) {
    super(props)

    this.$el = null
    this.$scroll = null
    this.$tape = null

    this.maxScrollTop = 0
    this.resizeHandler = this.onLayout.bind(this)

    this.state = {
      scrolling: false
    }
  }

  componentDidMount () {
    window.addEventListener('resize', this.resizeHandler)
    this.onLayout()
  }

  onLayout (evt) {
    this.maxScrollTop =
      this.$tape.getBoundingClientRect().height
      - this.$scroll.getBoundingClientRect().height
  }

  onScroll (evt) {
    let scrollTop = this.$scroll.scrollTop
    let scrolling = (
      this.props.flow === FLOW_UP && scrollTop < this.maxScrollTop ||
      this.props.flow === FLOW_DOWN && scrollTop > 0
    )

    if (this.state.scrolling !== scrolling) {
      this.state.scrolling = scrolling

      // don't use set state to prevent rerendering the element
      if (scrolling) {
        this.$el.classList.add('scrollable--scrolling')
      } else {
        this.$el.classList.remove('scrollable--scrolling')
      }
    }
  }

  componentDidUpdate () {
    this.onLayout()

    if (this.props.flow === FLOW_UP) {
      // set initial scroll position to the bottom
      this.$scroll.scrollTop = this.$scroll.scrollHeight
    }
  }

  componentDidUnmount () {
    window.removeEventListener('resize', this.resizeHandler)
  }

  render () {
    // compose class names
    let className = ['scrollable'].concat(
      this.props.classes,
      this.props.modifiers.map(modifier => 'scrollable--' + modifier),
      'scrollable--flow-' + this.props.flow,
      this.state.scrolling ? 'scrollable--scrolling' : []
    ).join(' ')

    return (
      <div className={ className } ref={ $el => this.$el = $el }>
        <div
          className="scrollable__scroll"
          ref={ $el => this.$scroll = $el }
          onScroll={ this.onScroll.bind(this) }>
          <div className="scrollable__tape" ref={ $el => this.$tape = $el }>
            { this.props.children }
          </div>
        </div>
      </div>
    )
  }
}
