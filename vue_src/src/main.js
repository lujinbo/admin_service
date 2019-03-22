// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
//全局引入ant design
import Antd from 'ant-design-vue'
import 'ant-design-vue/dist/antd.css'
Vue.use(Antd)
//引入axios
import axios from 'axios'
//引入实例
import App from './App'
import router from './router'
//引入Moment.js
//import moment from 'moment'
import moment from 'moment-timezone'
moment.locale('zh-cn')
moment.tz.setDefault('Asia/Bangkok')//曼谷时间
//引入图表
import VCharts from 'v-charts'
Vue.use(VCharts)
import QRCode from 'qrcode'
//************配置游戏编号，及正式服or测试服************//
let mode = 0
let game_id = 0
let game_name = "Admin_Service"
Vue.prototype.$game = game_id
Vue.prototype.$game_token = mode+'APPNAME'+game_id+'_token'
Vue.prototype.$game_user_name = mode+'APPNAME'+game_id+'_user_name'
Vue.prototype.$game_zch = mode == 1 ? game_name : game_name+'TEST'
document.title = Vue.prototype.$game_zch
Vue.config.productionTip = true
//***************************************************//
//正式服|测试服
axios.defaults.baseURL        = mode == 1 ? 'http://139.180.133.174:8080/' : 'http://139.180.133.174:8080/'
//原型属性
Vue.prototype.$img_host       = mode == 1 ? 'http://139.180.133.174:8080/image/' : '139.180.133.174:8080/image/'
Vue.prototype.$blind_api      = mode == 1 ? 'http://139.180.133.174:8080/blind.html?user_name=' : 'http://139.180.133.174:8080/blind.html?user_name='
Vue.prototype.$QR_login_api   = mode == 1 ? 'http://139.180.133.174:8080/blind.html?id=' : 'http://139.180.133.174:8080/blind.html?id='
// 添加请求拦截器
axios.interceptors.request.use((config) => {
    //如果有就携带token
    let _token = localStorage.getItem(Vue.prototype.$game_token)
    if (_token == null) {
      router.push('/login')
      return config;
    }
    let token = window.atob(_token)
    token = JSON.parse(token)
    let now = moment().valueOf()
    if (token.exp < now/1000) {
      router.push('/login')
    }else{
      config.headers.Authorization = _token;
    }
    return config;
  }, (error) => {
    // 对请求错误做些什么
    return Promise.reject(error);
  });
//接收拦截器
axios.interceptors.response.use((response) => {
    switch(response.data.status){
      case 403:
        router.push('/403')
        break;
      case 404:
        //router.push('/404')
        break;
      case 903:
        router.push('/login')
    }
    return response;
  }, (error) => {
    // 对响应错误做点什么
    return Promise.reject(error);
  });
Vue.prototype.$http = axios
Vue.prototype.$moment = moment
Vue.prototype.QRCode = QRCode
Vue.prototype.numFormate = (num) => {
  return num && num.toString().replace(/(?=(?!^)(\d{3})+$)/g, ',')
}
Vue.prototype.deepClone = (obj) => {
  let type = typeof(obj)
  if (type == 'array') {
    return Array.from(obj)
  }else{
    return JSON.parse(JSON.stringify(obj))
  }
}  
//实例化
/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  components: { App },
  template: '<App/>'
})
