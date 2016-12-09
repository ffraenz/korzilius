
import ReactDOM from 'react-dom'
import App from '../components/app/app'

/*
import moment from 'moment'
import 'moment/locale/lb'
moment.locale('lb')

console.log(moment.locale())
console.log(moment().calendar())
*/

let $root = document.querySelector('.app-wrapper')

ReactDOM.render(<App />, $root)
