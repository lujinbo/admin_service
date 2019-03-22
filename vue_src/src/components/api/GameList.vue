<template>
    <div class="com-item">
        <a-button type="primary" @click="showModal"><a-icon type="plus" />添加项目</a-button>
        <a-alert :message="alter.message" :type="alter.type" showIcon closable style="margin: 12px 0;"/>
        <a-table
            :columns="columns"
            :dataSource="data"
            :pagination="pagination"
            :loading="loading">
            <span slot="action" slot-scope="text, record">
                <a-button type="dashed" @click="editGame(text)">编辑</a-button>
                <a-button type="dashed" @click="editApi(record.id)">API管理</a-button>
            </span>
        </a-table>
        <a-modal title="添加项目" v-model="visible">
            <a-alert message="若项目下已注册有API，则该项目不可删除!" banner style="margin-bottom: 12px;"/>
            <a-form>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入项目名称" v-model="name">
                    </a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入项目代号" v-model="code">
                    </a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入项目简要描述" v-model="game_desc">
                    </a-input>
                </a-form-item>
            </a-form>
            <template slot="footer">
                <a-button @click="handleCancel">取消</a-button>
                <a-button type="primary" @click="handleOk">确认添加</a-button>
            </template>
        </a-modal>
        <a-modal title="编辑项目" v-model="edit_visible">
            <a-alert message="若项目下已注册有API，则该项目不可删除!" banner style="margin-bottom: 12px;"/>
            <a-form>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input v-model="name"></a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input v-model="code"></a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input v-model="game_desc"></a-input>
                </a-form-item>
            </a-form>
            <template slot="footer">
                <a-button @click="handleCancel">取消</a-button>
                <a-button type="primary" @click="handleEdit">确认修改</a-button>
            </template>
        </a-modal>
        <a-drawer title="权限编辑" placement="left"  :closable="false"  @close="onClose" :visible="drawer_visible" closable width="60%">
            <a-tabs defaultActiveKey="1" tabPosition="left">
                <a-tab-pane tab="Tab 1" key="1">Content of Tab 1</a-tab-pane>
                <a-tab-pane tab="Tab 2" key="2">Content of Tab 2</a-tab-pane>
                <a-tab-pane tab="Tab 3" key="3">Content of Tab 3</a-tab-pane>
            </a-tabs>
        </a-drawer>
    </div>
</template>
<script>
const columns = [
    {
        title: '项目ID',
        dataIndex:'id'
    },
    {
        title: '项目名称',
        dataIndex:'name'
    },
    {
        title: '项目代号',
        dataIndex:'code'
    },
    {
        title: '项目描述',
        dataIndex:'game_desc'
    },
    {
        title: '操作',
        key: 'action',
        scopedSlots: { customRender: 'action' },
    }
]
const API = 'index.php?a=Api&m=getGameList'
const ADD_API = 'index.php?a=Api&m=addGame'
const UPDATE_API = 'index.php?a=Api&m=updateGame'
const DEL_API = 'index.php?a=Admin&m=delAdmin'
export default{
    data () {
        return {
            tip_visible:true,
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
            name:'',
            code:'',
            game_desc:'',
            game:'1',
            drawer_visible:false,
            edit_visible:false,
            edit_id:0,
        }
    },
    methods:{
        showDrawer() {
            this.drawer_visible = true
        },
        onClose() {
            this.drawer_visible = false
        },
        showModal() {
            this.visible = true
        },
        fetch() {
            this.$http.get(API).then((res) => {
                this.loading = false
                if (res.data.status == 200) {
                    this.data = res.data.data;
                    this.alter = {message:'加载成功!', type:'success'}
                }else{
                    this.alter = {message:res.data.msg, type:'error'}
                }                    
            })
        },
        editGame(data){
            console.log(data)
            this.edit_visible = true
            this.name = data.name
            this.code = data.code
            this.game_desc = data.game_desc
            this.edit_id = data.id
        },
        handleOk() {
            if (this.name == '') {
                this.$error({ title: '非法的项目名称', content: '项目名称不可以为空，请重新输入...', })
                return false
            }
            let codeReg = /^[a-zA-Z0-9_-]{3,16}$/
            let is_code = codeReg.test(this.code)
            if (!is_code) {
                this.$error({ title: '非法的项目代码', content: '必须是3到16位（字母，数字，下划线，减号）...', })
                return false
            }
            if (this.game_desc == '') {
                this.$error({ title: '非法的项目描述', content: '项目描述不可以为空，请重新输入...', })
                return false
            }
            this.visible = false
            this.$message.loading('添加中...',0)
            let params = {
                name: this.name,
                code: this.code,
                game_desc: this.game_desc
            }
            this.$http.post(ADD_API, params).then((res) => {
                this.$message.destroy()//关闭loading
                if (res.data.status == 200) {                    
                    this.$success({ title: '添加项目成功!' })
                    this.fetch()
                }else{
                    this.$message.error('添加项目失败!',2)
                }
            });
        },
        handleEdit(){
            if (this.name == '') {
                this.$error({ title: '非法的项目名称', content: '项目名称不可以为空，请重新输入...', })
                return false
            }
            let codeReg = /^[a-zA-Z0-9_-]{3,16}$/
            let is_code = codeReg.test(this.code)
            if (!is_code) {
                this.$error({ title: '非法的项目代码', content: '必须是3到16位（字母，数字，下划线，减号）...', })
                return false
            }
            if (this.game_desc == '') {
                this.$error({ title: '非法的项目描述', content: '项目描述不可以为空，请重新输入...', })
                return false
            }
            this.edit_visible = false
            this.$message.loading('修改中...',0)
            let params = {
                id:this.edit_id,
                name: this.name,
                code: this.code,
                game_desc: this.game_desc
            }
            this.$http.post(UPDATE_API, params).then((res) => {
                this.$message.destroy()//关闭loading
                if (res.data.status == 200) {                    
                    this.$success({ title: '更新项目成功!' })
                    this.fetch()
                }else{
                    this.$error({ title: '更新项目失败!', content: '失败原因是:'+res.data.msg })
                }
            });
        },
        handleCancel() {
            this.visible = false
            this.edit_visible = false
            this.$message.destroy()
        },
        editApi(api_id){
            console.log('api_id:'+api_id)
            this.$router.push('/api/apiList/'+api_id)
        }
    },
    mounted() {
        this.fetch()
    },
} 
</script>