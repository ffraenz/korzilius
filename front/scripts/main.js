
import ReactDOM from 'react-dom'
import App from '../components/app/app'

// set moment locale to lb
import moment from 'moment/src/moment'
import locale from 'moment/src/locale/lb'
moment.locale('lb')

let $root = document.querySelector('.app-wrapper')
ReactDOM.render(<App />, $root)
