<template>
    <div class="index">
        <a-layout id="components-layout-demo-custom-trigger">
            <a-layout-sider
            :trigger="null"
            collapsible
            v-model="collapsed"
            >
            <div class="logo">
                <a href="">
                    <a-avatar :size="46" src="static/dummy.png" calss="logo_img"/>
                    <span>{{$game_zch}}</span>
                </a>
            </div>
            <a-menu theme="dark" mode="inline" :defaultSelectedKeys="['0']" @click="handleClick">
                <a-sub-menu v-for="(sub,index) in list" :key="index">
                    <span slot="title"><a-icon :type="sub.type" /><span>{{sub.title}}</span></span>
                    <a-menu-item :key="index +'_' + i" v-for="(item,i) in sub.item">
                        {{item.title}}
                    </a-menu-item>
                </a-sub-menu>
            </a-menu>
            </a-layout-sider>
            <a-layout>
                <a-layout-header style="background: #fff; padding: 0;box-shadow: 0 1px 4px rgba(0,21,41,.08);z-index: 100;">
                <a-icon
                class="trigger"
                :type="collapsed ? 'menu-unfold' : 'menu-fold'"
                @click="()=> collapsed = !collapsed"
                />
                <div class="header-right">
                    <div class="header-item">
                        <a-dropdown placement="bottomCenter">
                            <a-badge :count="100" :overflowCount="99"><a-icon type="bell" class="badge"/></a-badge>
                            <a-menu slot="overlay">
                                <a-menu-item>
                                    tom评论了您的最新朋友圈
                                </a-menu-item>
                                <a-menu-item>
                                    Jim给您点赞
                                </a-menu-item>
                        </a-menu>
                        </a-dropdown>                     
                    </div>
                    {{user_name}}
                    <div class="header-item">
                        <a-dropdown placement="bottomCenter">
                            <a-avatar :size="48" :src="avatar_head" :style="{backgroundColor: avatar_color}">{{user_name}}</a-avatar>
                            <a-menu slot="overlay">
                                <a-menu-item @click="showModal">
                                    <a-icon type="lock" />修改密码
                                </a-menu-item>
                                <a-menu-item @click="showQRModal">
                                    <a-icon type="qrcode" />绑定令牌
                                </a-menu-item>
                                <a-menu-item @click="logOut">
                                    <a-icon type="poweroff" />退出登录
                                </a-menu-item>
                        </a-menu>
                        </a-dropdown>                 
                    </div>
                </div>                
                </a-layout-header>
                <a-breadcrumb class="page-header">
                        <a-breadcrumb-item><a href="">主页</a></a-breadcrumb-item>
                        <a-breadcrumb-item>{{parent}}</a-breadcrumb-item>
                        <a-breadcrumb-item>{{self}}</a></a-breadcrumb-item>
                </a-breadcrumb>
                <!-- <a-layout-content :style="{ margin: '12px', padding: '16px', background: '#fff' }"> -->
                <a-layout-content :style="{ margin: '6px', padding: '8px' }">
                    <router-view></router-view>
                </a-layout-content>
            </a-layout>
        </a-layout>

        <a-modal title="修改密码" v-model="visible">
            <a-alert message="并请牢记您的新密码，否则只能联系PHP哦" banner style="margin-bottom: 12px;"/>
            <a-form>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入旧密码" v-model="user_pwd">
                        <a-icon slot="prefix" type="user" />
                    </a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入新密码,6-32位字符组合" v-model="new_pwd" type="password">
                        <a-icon slot="prefix" type="lock" />
                    </a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请重复输入新密码,6-32位字符组合" v-model="new_pwd_repeat" type="password">
                        <a-icon slot="prefix" type="lock" />
                    </a-input>
                </a-form-item>           
                <a-row>
                    <a-col :span="4">
                        <label>密码强度:</label>
                    </a-col>
                    <a-col :span="20">
                        <a-progress :percent="20+20*pwd_level" size="small" :strokeColor="getColor(pwd_level)" :format="() => getText(pwd_level)"/>
                    </a-col>
                </a-row>
            </a-form>
            <template slot="footer">
                <a-button @click="handleCancel">取消</a-button>
                <a-button type="primary" @click="handleOk" :disabled="allow_change_pwd">确认修改</a-button>
            </template>
        </a-modal>

        <a-modal title="绑定手机令牌" v-model="qr_visible">
            <a-alert message="微信扫描此二维码，提示【绑定成功】即完成操作，以后即可使用微信扫码完成登录验证!重要信息，请勿泄露至他人!以防账号被盗" banner/>
            <div style="text-align: center;">
                <canvas id="qrccode-blind"></canvas>
            </div>
            <a-row>
                <a-col :span="12" style="text-align: right;padding: 0.1rem 0.5rem;">
                    <a-icon type="scan" style="color: #00c1de;font-size: 2rem;"/>
                </a-col>
                <a-col :span="12" style="font-size: 0.5rem;">
                    <p style="margin-bottom: 0;">打开<span style="color: #00c1de;">微信</span></p>
                    <p style="margin-bottom: 0;">扫一扫绑定</p>
                </a-col>
            </a-row>                     
            <template slot="footer">
                <span></span>
            </template>
        </a-modal>
    </div>
</template>

<script>
    const API = 'index.php?a=Admin&m=changeAdminPassword'
    const GET_SIGN_API = 'index.php?a=Admin&m=getSignature'
    const PWD_LEVEL = ['极弱','较弱','一般','较强','极强']
    const COLOR = ['#F56C6C','#E6A23C','#409EFF','#67C23A','green']
    export default {
        data(){
            return {
                collapsed: false,
                avatar_color:'#00a2ae',
                avatar_head:"",
                parent:'默认',
                self:'欢迎页',
                list:[
                        {
                            title:'管理员',
                            type:'safety',
                            item:[
                                {title:'管理员列表', path:'/admin/adminlist'},
                            ]
                        },
                        {
                            title:'API管理',
                            type:'profile',
                            item:[
                                {title:'项目列表', path:'/api/gameList'},
                            ]
                        },
                    ],
                visible:false,
                user_pwd:'',
                new_pwd:'',
                new_pwd_repeat:'',
                qr_visible:false,
                allow_change_pwd:true,
                PWD_LEVEL:PWD_LEVEL,
                pwd_level:0,
            }
        },
        watch:{
            new_pwd(val){
                let n = this.checkPassWordLevel(val)
                this.pwd_level = n
                this.allow_change_pwd = n > 1 ? false : true
            }
        },
        methods:{
            getText(level){
                return PWD_LEVEL[level]
            },
            getColor(level){
                return COLOR[level]
            },
            handleClick (e) {
                //处理面包屑显示
                let parent = e.keyPath[1]
                this.parent = this.list[parent].title
                let self = e.keyPath[0].charAt(e.keyPath[0].length - 1)
                this.self = this.list[parent].item[self].title
                //路由跳转
                let to = this.list[parent].item[self].path
                this.$router.push(to)
            },
            handleOk() {
                if (this.user_pwd == '') {
                    this.$message.error('旧密码不能为空!',1)
                    return false
                }
                if (this.new_pwd == '') {
                    this.$message.error('新密码不能为空!',1)
                    return false
                }
                if (this.new_pwd.length > 32) {
                    this.$message.error('新密码长度不能超过32位!',1)
                    return false
                }
                //先检查新密码是否一致
                if (this.new_pwd_repeat != this.new_pwd) {
                    this.$message.error('两次输入的新密码不一致!',2)
                    return false
                }
                this.visible = false
                this.$message.loading('修改密码中...',3)
                let params = {
                    user_name: this.user_name,
                    user_pwd: this.user_pwd,
                    new_pwd: this.new_pwd
                }
                let that = this
                this.$http.post(API, params).then((res) => {
                    that.$message.destroy()//关闭loading
                    that.user_pwd = that.new_pwd = that.new_pwd_repeat = ''
                    if (res.data.status == 200) {
                        that.$message.success(res.data.msg,2)
                    }else{
                        that.$message.error(res.data.msg,2)
                    }
                })
            },
            handleCancel() {
                this.visible = false
                this.qr_visible = false
                this.$message.destroy()
            },
            showModal() {
                this.visible = true
            },
            showQRModal(){
                this.qr_visible = true
                let params = {user_name:this.user_name}
                this.$http.get(GET_SIGN_API, {params:params}).then((res) => {
                    if (res.data.status == 200) {
                        this.$nextTick(function () {
                            this.createQR(this.$blind_api+this.user_name+'&signature='+res.data.signature)
                        })
                    }                    
                })                
            },
            createQR(signature){
                let canvas = document.getElementById('qrccode-blind')
                this.QRCode.toCanvas(canvas,signature,(res) => {
                })
            },
            logOut() {
                localStorage.removeItem(this.$game_user_name)
                localStorage.removeItem(this.$game_token)
                const modal_success = this.$success({
                            title: '退出成功!',
                            content: '将在1秒后跳转至登录界面~'
                        })
                setTimeout(() =>{ modal_success.destroy();this.$router.push('/login')}, 1000)
            },
            checkPassWordLevel(val){
                let n = 0
                if (/\d/.test(val)) n ++
                if (/[a-z]/.test(val)) n ++
                if (/[A-Z]/.test(val)) n ++
                if (/\W/.test(val)) n ++;
                val.length > 5 ? n : n = 0;
                if (val.length > 9) n++;
                return n;
            }
        },
        computed:{
            user_name(){
                return localStorage.getItem(this.$game_user_name)
            }
        },
        mounted:function () {
            let _token = localStorage.getItem(this.$game_token)
            if (_token == null) {
                this.$router.push('/login')
                return false
            }
            let token = window.atob(_token)
            token = JSON.parse(token)
            let now = this.$moment().valueOf()
            if (token.exp < now/1000) {
                this.$router.push('/login')
            }
        }
}
</script>

<style>
.index{
    width: 100%;
    height: 100%;
}
#components-layout-demo-custom-trigger{
    text-align: left;
    width: 100%;
    height: 100%;
}
#components-layout-demo-custom-trigger .trigger {
    font-size: 18px;
    line-height: 64px;
    padding: 0 24px;
    cursor: pointer;
    transition: color .3s;
}

#components-layout-demo-custom-trigger .trigger:hover {
    color: #1890ff;
}

#components-layout-demo-custom-trigger .logo {
    height: 48px;
    margin: 16px;
    overflow: hidden;
}
#components-layout-demo-custom-trigger .logo .logo_img{
    max-height: 100%;
    max-width: 100%
}
#components-layout-demo-custom-trigger .logo span{
    color: #FFF;
    font-size: 16px;
    font-weight:bold
}
#components-layout-demo-custom-trigger .header-right{
    float: right;
    margin-right: 56px;
}
.page-header{
    background: #fff;
    padding: 16px 24px;
    box-shadow: 0 1px 4px rgba(0,21,41,.08);
}
.page-header h2{
    margin-top: 8px;
    margin-bottom: 0px;
}
#components-layout-demo-custom-trigger .header-right .badge{
    font-size: 18px;
    line-height: 24px;
    padding: 0 12px;
    cursor: pointer;
}
#components-layout-demo-custom-trigger .header-right .header-item{
    display: inline;
    padding: 0 16px;
}
</style>