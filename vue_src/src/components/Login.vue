<template>
<div v-bind:style="login_box_style" class="login-box">
    <div>
        <a-row>
            <a-col :xs="2" :sm="4" :md="6" :lg="8" :xl="10"></a-col>
            <a-col :xs="20" :sm="16" :md="12" :lg="8" :xl="4">
                <p>{{$game_zch}}项目管理后台</p>
                <br/>
                <div style="position: relative;">
                    <a-form class='login-form' v-if="!show_qr">
                        <a-tabs defaultActiveKey="1" @change="callback">
                            <a-tab-pane tab="用户名/密码登录" key="1">
                                <a-form-item :validateStatus="validate.user_name.status" :help="validate.user_name.help">
                                    <a-input placeholder='Username' size="large" v-model="user_name" auto-focus>
                                        <a-icon slot="prefix" type="user" />
                                    </a-input>
                                </a-form-item>
                                <a-form-item :validateStatus="validate.user_pwd.status" :help="validate.user_pwd.help" v-if="!show_qr">
                                    <a-input type='password' placeholder='Password' size="large" v-model="user_pwd" @keyup.enter="handleSubmit">
                                        <a-icon slot="prefix" type='lock' />
                                    </a-input>
                                </a-form-item>
                            </a-tab-pane>
                            <a-tab-pane tab="邮箱验证码登录" key="2">
                                <a-form-item :validateStatus="validate.user_mail.status" :help="validate.user_mail.help">
                                    <a-input-search placeholder="E-mail" @search="getCode" size="large" v-model="user_mail" auto-focus>
                                        <a-button slot="enterButton" style="font-size: 12px;" :disabled="get_code_btn">{{code_btn_text}}</a-button>
                                    </a-input-search>
                                </a-form-item>
                                <a-form-item v-if="show_code_input">
                                    <a-row type="flex" justify="space-around" style="text-align: center;">
                                        <a-col :span="4"><a-input style="width: 2.7rem;" size="large" ref="input1" v-model="code1"/></a-col>
                                        <a-col :span="4"><a-input style="width: 2.7rem;" size="large" ref="input2" v-model="code2"/></a-col>
                                        <a-col :span="4"><a-input style="width: 2.7rem;" size="large" ref="input3" v-model="code3"/></a-col>
                                        <a-col :span="4"><a-input style="width: 2.7rem;" size="large" ref="input4" v-model="code4"/></a-col>
                                        <a-col :span="4"><a-input style="width: 2.7rem;" size="large" ref="input5" v-model="code5"/></a-col>
                                        <a-col :span="4"><a-input style="width: 2.7rem;" size="large" ref="input6" v-model="code6"/></a-col>
                                    </a-row>
                                </a-form-item>
                            </a-tab-pane>
                        </a-tabs>
                        <a-form-item class='login-form-item'>
                            <a-checkbox class='login-form-remeber' v-model="is_remeber">自动登录</a-checkbox>
                            <a class='login-form-forgot'>忘记密码</a>
                        </a-form-item>
                        <a-button type='primary' class='login-form-button' size="large" @click="handleSubmit">登录</a-button>
                    </a-form>
                    <div v-if="show_qr">
                        <canvas id="qrccode-canvas" style="margin-top: 4rem;"></canvas>
                    </div>
                    <div v-if="show_qr">
                        <a-row>
                            <a-col :span="12" style="text-align: right;padding: 0.1rem 0.5rem;">
                                <a-icon type="scan" style="color: #00c1de;font-size: 2rem;"/>
                            </a-col>
                            <a-col :span="10" style="font-size: 0.5rem;text-align: left;">
                                <p style="margin: 0;font-size: 0.5rem;">打开<span style="color: #00c1de;font-size: 0.5rem;">微信</span></p>
                                <p style="margin: 0;font-size: 0.5rem;">扫一扫登录</p>
                            </a-col>
                        </a-row>
                    </div>                  
                </div>                
                <div style="position: absolute;right: 0;top: 150px;">
                    <a-tooltip placement="left" v-model="tip_visible" @visibleChange="visibleChange">
                    <template slot="title">
                        <span style="color: #00c1de;">{{switch_text}}</span>
                    </template>
                    <img :src="login_qr_img" style="width: 3rem;height: 3rem;" @click="handleSwitch">
                    </a-tooltip>                
                </div>                
                <footer class="footer">
                    Copyright <a-icon type="copyright" /> {{new Date().getFullYear()}} Shenzhen
                </footer>
            </a-col>
            <a-col :xs="2" :sm="4" :md="6" :lg="8" :xl="10"></a-col>
        </a-row>        
    </div>
</div>
</template>

<script>
//请求登录地址
const API                   = 'index.php?a=Admin&m=login'
const GET_CODE_API          = 'index.php?a=Admin&m=getCode'
const CHECK_CODE_API        = 'index.php?a=Admin&m=checkCode'
const GET_QR_API            = 'index.php?a=Admin&m=getUniqueQRCode'
const GET_LOGIN_INFO_API    = 'index.php?a=Admin&m=getLoginInfo'
//status 枚举值 enum
const status = ['validating', 'warning', 'error', 'success']
export default {
    name: 'test',
    data () {
        return {
            code1:'',
            code2:'',
            code3:'',
            code4:'',
            code5:'',
            code6:'',
            tip_visible:true,
            login_box_style:{
                height:document.documentElement.clientHeight + 'px'
            },
            user_name:'',
            user_pwd:'',
            //user_mail:'terence@mytopfun.com',
            user_mail:'@mytopfun.com',
            validate:{
                user_name:{
                    status:'',
                    help:''
                },
                user_pwd:{
                    status:'',
                    help:''
                },
                user_mail:{
                    status:'',
                    help:''
                }
            },
            is_remeber:true,
            tab:'1',
            code_btn_text:'发送验证码',
            get_code_btn:true,
            show_code_input:false,
            numReg:/^\d{1}$/,
            qr_id:'',
            show_qr:false,
            login_qr_img:'static/qrcode.png',
            switch_text:'扫码登录',
            timmer:null
        }
    },
    methods:{
        visibleChange(visible){
            this.tip_visible = true
        },
        onChange(value){
            console.log('changed', value);
        },
        getCode(){
            this.code_btn_text = '验证码发送中...'
            let params = {
                user_mail:this.user_mail
            }
            this.$http.post(GET_CODE_API, params).then((res) => {
                if (res.data.status == 200) {
                    this.$success({
                        title: '验证码发送成功',
                        content: '验证码已发送至'+this.user_mail+'，5分钟内有效，请及时登录获取，1分钟后可重新发送验证码...'
                    })
                    this.show_code_input = true
                    this.$nextTick(() =>{
                        this.$refs.input1.focus()
                    })                    
                    this.get_code_btn = true
                    this.handleMins()
                }else{
                    this.code_btn_text = '发送验证码'
                    this.setValidate('user_mail',status[2],res.data.msg)
                }
            });
        },
        handleMins(){
            let num = 60
            let timer = setInterval(() => {
                num--
                if (num < 1) {
                    this.get_code_btn = false
                    this.code_btn_text = '发送验证码'
                    clearInterval(timer)
                }else{
                    this.code_btn_text = num+'后重新发送'
                }
            },1000)
        },
        callback (key) {
            this.tab = key
        },
        //@todo 监听变化,实时跳出提醒
        handleSubmit () {
            if (this.tab == '1') {
                //提交Ajax请求前，校验各参数是否合法
                let error = this.checkBeforeLogin()
                if(error !== 0) {
                    return false
                }
                this.user_info.game = this.$game
                this.$http.post(API, this.user_info).then((res) => {
                    this.handleSuccess(res.data)
                });
            }
            if (this.tab == '2') {
                this.submitCode()
            }         
        },
        //输入框友好提醒
        setValidate (attr, status = '', help = '') {
            this.validate[attr].status = status
            this.validate[attr].help = help
        },
        //校验参数是否合法并给出提示
        checkBeforeLogin () {
            let that = this
            let error = 0;
            Object.keys(this.user_info).forEach((v) => {
                if (that.user_info[v] == '') {
                    that.setValidate(v,status[1],'此处不能为空')
                    error++
                }
            })
            return error
        },
        //请求成功后的回调
        handleSuccess (res) {
            switch (res.status){
                    case 200:
                        //加载路由
                        localStorage.setItem(this.$game_token,res.token)
                        localStorage.setItem(this.$game_user_name,this.user_name)
                        const modal_success = this.$success({
                            title: '验证成功!',
                            content: '将在1秒后跳转至后台主界面~'
                        })
                        setTimeout(() =>{ modal_success.destroy();this.$router.push('/')}, 1000)
                        break;
                    case 702:
                        this.$error({ title: '错误', content: '参数不足...' })
                        break;
                    case 901:
                        this.setValidate('user_name',status[2],'用户不存在')
                        break;
                    case 902:
                        this.setValidate('user_pwd',status[2],'密码错误')
                        break;
                    case 905:
                        this.$error({
                            title: '验证码错误',
                            content: '验证码错误或已过期，请重新获取...'
                        })
                        this.get_code_btn = false
                        this.code_btn_text = '发送验证码'
                        break;
                    default:
                        this.$error({
                            title: '未知错误',
                            content: '登录时发生了未知的错误，请联系系统管理员...'
                        })
                }
            return false
        },
        submitCode(){
            let code = this.code1+this.code2+this.code3+this.code4+this.code5+this.code6;
            let params = { user_mail:this.user_mail,code:code, game:this.$game }
            this.$http.post(CHECK_CODE_API, params).then((res) => {
                this.user_name = res.data.name
                this.handleSuccess(res.data)
            });
        },
        handleSwitch(){
            this.show_qr = !this.show_qr
            if (this.show_qr) {
                this.switch_text = '密码登录'
                this.login_qr_img = 'static/computer.png'
                this.getQRCode()
            }else{
                clearInterval(this.timmer)
                this.switch_text = '扫码登录'
                this.login_qr_img = 'static/qrcode.png'
            }
        },
        getQRCode(){
            //先申请一个唯一标识符
            this.$http.get(GET_QR_API).then((res) => {
                this.qr_id = res.data.id
                this.show_qr = true
                this.$nextTick(() =>{
                    this.createQR()
                })
            });
        },
        createQR(){
            let canvas = document.getElementById('qrccode-canvas')
            console.log(this.qr_id)
            this.QRCode.toCanvas(canvas,this.$QR_login_api+this.qr_id,(res) => {})
            this.timmer = setInterval(() => {
                this.getLoginInfo()
            },2000)
        },
        getLoginInfo(){
            let params = {id:this.qr_id,game:this.$game }
            this.$http.get(GET_LOGIN_INFO_API,{params:params}).then((res) => {
                console.log(res.data)
                switch (res.data.status){
                    case 200:
                        clearInterval(this.timmer)
                        this.user_name = res.data.name
                        //加载路由
                        localStorage.setItem(this.$game_token,res.data.token)
                        localStorage.setItem(this.$game_user_name,this.user_name)
                        const modal_success = this.$success({
                            title: '验证成功!',
                            content: '将在1秒后跳转至后台主界面~'
                        })
                        setTimeout(() =>{ modal_success.destroy();this.$router.push('/')}, 1000)
                        break;
                    case 702:
                        this.$error({ title: '错误', content: '参数不足...' })
                        clearInterval(this.timmer)
                        break;
                    case 901:
                        this.$error({ title: '错误', content: '被非法的用户扫码...' })
                        clearInterval(this.timmer)
                        break;
                    case 801:
                        this.$error({ title: '错误', content: '二维码已过期...' })
                        clearInterval(this.timmer)
                        break;
                }
                return false
            })
        }
    },
    computed:{
        user_info () {
            return {user_name:this.user_name, user_pwd:this.user_pwd }
        }
    },
    watch:{
        user_name (val) {
            if ('' == val) {
                this.setValidate('user_name',status[1],'用户名不能为空')
            }else{
                this.setValidate('user_name')
            }
        },
        user_pwd (val) {
            if ('' == val) {
                this.setValidate('user_pwd',status[1],'密码不能为空')
            }else{
                this.setValidate('user_pwd')
            }
        },
        user_mail(val){
            let mailReg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/
            let is_mail = mailReg.test(val)
            is_mail ? this.get_code_btn = false : true
            if (is_mail) { this.setValidate('user_mail') }
        },
        code1(val){
            if (val != '' && this.numReg.test(val)) {
                this.$nextTick(() => {
                    this.$refs.input2.focus()
                })
            }else{
                this.code1 = ''
            }
        },
        code2(val){
            if (val != '' && this.numReg.test(val)) {
                this.$nextTick(() => {
                    this.$refs.input3.focus()
                })
            }else{
                this.code2 = ''
            }
        },
        code3(val){
            if (val != '' && this.numReg.test(val)) {
                this.$nextTick(() => {
                    this.$refs.input4.focus()
                })
            }else{
                this.code3 = ''
            }
        },
        code4(val){
            if (val != '' && this.numReg.test(val)) {
                this.$nextTick(() => {
                    this.$refs.input5.focus()
                })
            }else{
                this.code4 = ''
            }
        },
        code5(val){
            if (val != '' && this.numReg.test(val)) {
                this.$nextTick(() => {
                    this.$refs.input6.focus()
                })
            }else{
                this.code5 = ''
            }
        },
        code6(val){
            if (val != '' && this.numReg.test(val)) {
                this.submitCode()
            }else{
                this.code6 = ''
            }
        },
    },
    mounted:function () {
        //code
    }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.login-box{
    background-image: url('../../static/login_bg.svg');
    text-align: center;
    background-color: rgb(240,242,245);
}
.login-form-item{
    margin-bottom: 10px;
    margin-top: 1rem;
}
.login-box img{
    max-width: 100%;
    margin-top: 100px;
}
.login-box p{
    font-size: 1.5rem;
    margin-top: 10rem;
    color:rgba(0,0,0,.45);
}
.login-form{
    margin-top: 48px;
    text-align: left;
    line-height: 24px;
}
.login-form-forgot {
  float: right;
}
.login-form-button {
  width: 100%;
}
.login-form-remeber{
    float: left;
}
.footer{
    margin: 48px 0 24px;
    color:rgba(0,0,0,.45);
    position: fixed;
    bottom: 0px;
    left: 0;
    right: 0;
}
</style>