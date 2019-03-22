import Vue from 'vue'
import Router from 'vue-router'


//引入组件 懒加载
const Login                   = () => import('@/components/Login')
const Index                   = () => import('@/components/Index')
const Dashboard               = () => import('@/components/Dashboard')
//error
const NotAllowed              = () => import('@/components/errors/403')
const NotFound                = () => import('@/components/errors/404')
const ServerError             = () => import('@/components/errors/500')


//管理员
const AdminList               = () => import('@/components/admin/AdminList')
const AdminApi                = () => import('@/components/admin/AdminApi')
const LoginRecord             = () => import('@/components/admin/LoginRecord')
const RequestRecord             = () => import('@/components/admin/RequestRecord')
//API
const GameList                = () => import('@/components/api/GameList')
const ApiList                 = () => import('@/components/api/ApiList')

Vue.use(Router)

//所有权限通用路由表 
//如首页和登录页和一些不用权限的公用页面
export const constantRouterMap = [
  { path: '/login', component: Login },
  {
    path: '/',
    component: Index,
    children:[
        { path: '/',                          component: Dashboard },
        //管理员
        { path: '/admin/adminList',                               component: AdminList },
        { path: '/admin/adminApi/:user_name/:game_id',            component: AdminApi },
        { path: '/admin/loginRecord/:user_name',                  component: LoginRecord },
        { path: '/admin/requestRecord/:user_name',                component: RequestRecord },
        //api
        { path: '/api/gameList',              component: GameList },
        { path: '/api/apiList/:game_id',      component: ApiList, props: true},
        //
        { path: '/403', component: NotAllowed },
        { path: '/404', component: NotFound },
        { path: '/500', component: ServerError },
    ]
  },
  { path: '*', redirect: '/404', hidden: true }
]
export const asyncRouterMap = []

export default new Router({
  //mode:'history',
  routes: constantRouterMap
})
