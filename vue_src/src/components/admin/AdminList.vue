<template>
    <div class="com-item">        
        <a-input-search style="width: 200px;" v-model="keyword"/>
        <a-button type="primary" @click="showModal"><a-icon type="plus" />添加管理员</a-button>       
        <a-alert :message="alter.message" :type="alter.type" showIcon closable style="margin: 12px 0;"/>
        <a-table
            :columns="columns"
            :dataSource="table_data"
            :pagination="pagination"
            :loading="loading">
            <span slot="action" slot-scope="text, record">
                <a-button type="dashed" @click="editPermission(record.user_name)">编辑权限</a-button>
                <a-button @click="getLoginRecord(record.user_name)">登录记录</a-button>
                <a-button @click="getRequestRecord(record.user_name)">请求记录</a-button>
                <a-button type="danger" @click="delAdmin(record.user_name)">删除</a-button>
            </span>
        </a-table>
        <a-modal title="添加管理员" v-model="visible">
            <a-alert message="默认密码为topfun,登陆后请及时修改密码" banner style="margin-bottom: 12px;"/>
            <a-form>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入管理员名称" v-model="user_name" @blur="handleDefaultMail">
                        <a-icon slot="prefix" type="user" />
                    </a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入管理员邮箱" v-model="user_mail">
                        <a-icon slot="prefix" type="mail" />
                    </a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-select placeholder='选择管理员群组' v-model="user_group">
                        <a-select-option value='0'>超级管理员</a-select-option>
                        <a-select-option value='1'>普通管理员</a-select-option>
                    </a-select>
                </a-form-item>
            </a-form>
            <template slot="footer">
                <a-button @click="handleCancel">取消</a-button>
                <a-button type="primary" @click="handleOk">确认添加</a-button>
            </template>
        </a-modal>
        <a-modal  title="请先选择项目......" v-model="select_visible" @ok="handleSelect">
            <a-select style="width: 100%;" v-model="game_id">
                <a-select-option  v-for="(game,index) in game_list" :value="game.id" :key=index>{{game.name}}</a-select-option>
            </a-select>
        </a-modal>
    </div>
</template>
<script>
const columns = [
    {
        title: '管理员ID',
        dataIndex:'id'
    },
    {
        title: '管理员账号',
        dataIndex:'user_name'
    },
    {
        title: '管理员邮箱',
        dataIndex:'user_mail'
    },
    {
        title: '管理员组别',
        dataIndex:'user_group'
    },
    {
        title: '管理员状态',
        dataIndex:'status'
    },
    {
        title: '创建时间',
        dataIndex:'create_time'
    },
    {
        title: '操作',
        key: 'action',
        scopedSlots: { customRender: 'action' },
    }
]
const api       = 'index.php?a=Admin&m=getAdminList'
const add_api   = 'index.php?a=Admin&m=addAdmin'
const DEL_API   = 'index.php?a=Admin&m=delAdmin'
const GAME_LIST = 'index.php?a=Api&m=getGameList'
export default{
    data () {
        return {
            columns:columns,
            loading:false,
            data:[],
            pagination:{
                pageSize:10
            },
            alter:{
                message:'数据加载中!',
                type:'info'
            },
            visible: false,
            user_name:'',
            user_mail:'',
            user_group:'1',
            select_visible:false,
            game_list:[],
            select_user_name:'',
            game_id:'下拉选择要编辑的管理员权限所属项目...',
            keyword:''
        }
    },
    computed:{
        table_data(){
            if (this.keyword == '') {
                return this.data
            }else{
                let arr = this.data.filter((v) => {
                    return v.user_name.indexOf(this.keyword) != -1 || v.user_mail.indexOf(this.keyword) != -1
                })
                return arr
            }             
        }
    },
    methods:{
        showModal() {
            this.visible = true
        },
        fetch() {
            this.$http.get(api).then((res) => {
                this.loading = false
                if (res.data.status == 200) {
                    this.data = res.data.data;
                    this.alter = {message:'加载成功!', type:'success'}
                }else{
                    this.alter = {message:res.data.msg, type:'error'}
                }                    
            })
        },
        handleOk() {
            let nameReg = /^[a-zA-Z0-9_-]{3,16}$/
            let is_name = nameReg.test(this.user_name)
            if (!is_name) {
                this.$error({  title: '非法的用户名', content: '必须是3到16位（字母，数字，下划线，减号）...' })
                return false
            }
            let mailReg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/
            let is_mail = mailReg.test(this.user_mail)
            if (!is_mail) {
                this.$error({  title: '非法的邮件格式', content: '请输入正确的邮箱后重试...' })
                return false
            }
            this.visible = false
            this.$message.loading('添加中...',0)
            let params = {
                user_name: this.user_name,
                user_mail: this.user_mail,
                user_group: this.user_group
            }
            this.$http.post(add_api, params).then((res) => {
                this.$message.destroy()//关闭loading
                if (res.data.send_mail == 0) {}
                if (res.data.status == 200 && res.data.send_mail == 1) {                  
                    this.$success({ title: '添加管理员成功!', content: '密码是:'+res.data.pwd+',已发送至:'+this.user_mail })
                }
                if (res.data.status == 200 && res.data.send_mail == 0) {
                    this.$warning({ title: '添加管理员成功!', content: '密码是:'+res.data.pwd+',通知邮件发送失败!' })
                }
                if (res.data.status != 200) {
                    this.$error({ title: '添加管理员失败!', content: '原因是:'+res.data.msg })
                }                
                this.fetch()
            });
        },
        handleCancel() {
            this.visible = false
            this.$message.destroy()
        },
        handleDefaultMail(){
            if (this.user_mail == '') {
                this.user_mail = this.user_name+'@mytopfun.com'
            }
        },
        delAdmin(user_name){
            let that = this
            let params = {user_name:user_name}
            this.$confirm({
                title: '确认删除管理员:'+user_name+'吗?',
                content: '删除后不可恢复，请谨慎操作!',
                okText: '确认删除',
                okType: 'danger',
                cancelText: '取消',
                onOk() {
                    that.$http.post(DEL_API, params).then((res) => {
                        that.$message.destroy()//关闭loading
                        if (res.data.status == 200) {
                        that.data.forEach((v,k) => {
                            if (v.user_name == user_name) {
                                that.data.splice(k,1)
                            }
                        })                
                            that.$message.success('删除管理员成功!',2)
                        }else{
                            that.$message.error('删除管理员失败!',2)
                        }
                    });
                }
          });
        },
        getGameList(){
            this.$http.get(GAME_LIST).then((res) => {
                this.loading = false
                if (res.data.status == 200) {
                    this.game_list = res.data.data;
                    this.alter = {message:'加载成功!', type:'success'}
                }else{
                    this.alter = {message:res.data.msg, type:'error'}
                }                    
            })
        },
        editPermission(user_name){
            this.select_user_name = user_name
            this.select_visible = true
        },
        handleSelect(){
            if (this.select_user_name == '') {
                this.$error({ title: '非法用户名', content: '请先选择要编辑的管理员...' })
                return false
            }
            this.$router.push('/admin/adminApi/'+this.select_user_name+'/'+this.game_id)
        },
        getLoginRecord(user_name){
            this.$router.push('/admin/loginRecord/'+user_name)
        },
        getRequestRecord(user_name){
            this.$router.push('/admin/requestRecord/'+user_name)
        }
    },
    mounted() {
        this.fetch()
        this.getGameList()
    },
} 
</script>