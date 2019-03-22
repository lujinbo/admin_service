<template>
    <div class="com-item">
        <a-input-search style="width: 200px;" v-model="keyword"/>
        <a-button type="danger">
            正在编辑【<span style="color: #409EFF;">{{edit_game_name}}</span>】项目<a-icon type="right" />
        </a-button>        
        <a-tooltip placement="rightBottom">
            <template slot="title">
                <span>必须成功注册API后，才可访问资源!</span>
            </template>
            <a-button type="primary" @click="showModal"><a-icon type="plus" />注册API</a-button>
        </a-tooltip>
        <a-alert :message="alter.message" :type="alter.type" showIcon closable style="margin: 12px 0;"/>
        <a-table
            :columns="columns"
            :dataSource="table_data"
            :pagination="pagination"
            :loading="loading">
            <span slot="game" slot-scope="text, record">{{edit_game_name}}</span>
            <span slot="action" slot-scope="text, record">
                <a-button type="dashed" @click="editPermission(text)">编辑</a-button>
            </span>
        </a-table>
        <a-modal title="注册API" v-model="visible">
            <a-alert message="必须成功注册API后，才可访问资源!" banner style="margin-bottom: 12px;"/>
            <a-form>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入API名称" v-model="api_name">
                    </a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input placeholder="请输入API简要描述" v-model="api_desc">
                    </a-input>
                </a-form-item>
            </a-form>
            <template slot="footer">
                <a-button @click="handleCancel">取消</a-button>
                <a-button type="primary" @click="handleOk">确认添加</a-button>
            </template>
        </a-modal>
        <a-modal title="编辑API" v-model="edit_visible">
            <a-alert message="编辑后务必确保该API已经实现，否则会报404错误!" banner style="margin-bottom: 12px;"/>
            <a-form>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input v-model="api_name"></a-input>
                </a-form-item>
                <a-form-item style="margin-bottom: 12px;">
                    <a-input v-model="api_desc"></a-input>
                </a-form-item>
            </a-form>
            <template slot="footer">
                <a-button @click="handleCancel">取消</a-button>
                <a-button type="primary" @click="handleEdit">确认修改</a-button>
            </template>
        </a-modal>
    </div>
</template>
<script>
const columns = [
    {
        title: 'API_ID',
        dataIndex:'id'
    },
    {
        title: '所属游戏',
        dataIndex:'game',
        scopedSlots: { customRender: 'game' },
    },
    {
        title: 'API名称',
        dataIndex:'api_name'
    },
    {
        title: 'API描述',
        dataIndex:'api_desc'
    },
    {
        title: '操作',
        key: 'action',
        scopedSlots: { customRender: 'action' },
    }
]
const API               = 'index.php?a=Api&m=getApiList'
const ADD_API           = 'index.php?a=Api&m=addApi'
const UPDATE_API        = 'index.php?a=Api&m=updateApi'
const GET_GAME_NAME     = 'index.php?a=Api&m=getGameName'
export default{
    props: {
        game_id:{
            type: String,
            default: '0',
        }
    },
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
            api_name:'',
            api_desc:'',
            game:'1',
            edit_visible:false,
            edit_id:0,
            edit_game_name:'',
            keyword:''
        }
    },
    computed:{
        table_data(){
            if (this.keyword == '') {
                return this.data
            }else{
                let arr = this.data.filter((v) => {
                    return v.api_name.indexOf(this.keyword) != -1 || v.api_desc.indexOf(this.keyword) != -1
                })
                return arr
            }             
        }
    },
    methods:{
        showModal() {
            this.visible = true
        },
        fetch(game_id) {
            this.$http.get(API+'&id='+game_id).then((res) => {
                this.loading = false
                if (res.data.status == 200) {
                    this.data = res.data.data;
                    this.alter = {message:'加载成功!', type:'success'}
                }else{
                    this.alter = {message:res.data.msg, type:'error'}
                }                    
            })
        },
        editPermission(data){
            this.api_name = data.api_name
            this.edit_id = data.id
            this.api_desc = data.api_desc
            this.edit_visible = true
        },
        handleOk() {
            let params = {
                api_name: this.api_name,
                api_desc: this.api_desc,
                game:this.game_id
            }
            let nameReg = /^[a-zA-Z-_]{5,}$/
            let is_name = nameReg.test(this.api_name)
            if (!is_name) {
                this.$error({ title: '非法的API名称', content: '必须是5位以上（字母，数字，下划线，减号）...' })
                return false
            }
            if (this.api_desc == '') {
                this.$error({ title: '非法的API描述', content: '不可以为空）...' })
                return false
            }
            this.visible = false
            this.$message.loading('注册中...',0)
            this.$http.post(ADD_API, params).then((res) => {
                this.$message.destroy()//关闭loading
                if (res.data.status == 200) {                    
                    this.$success({ title: '注册API成功!' })
                    this.fetch(this.game_id)
                }else{
                    this.$message.error('注册API失败!',2)
                }
            });
        },
        handleEdit(){
            let params = {
                api_name: this.api_name,
                api_desc: this.api_desc,
                id: this.edit_id
            }
            let nameReg = /^[a-zA-Z-_]{5,}$/
            let is_name = nameReg.test(this.api_name)
            if (!is_name) {
                this.$error({ title: '非法的API名称', content: '必须是5位以上（字母，数字，下划线，减号）...' })
                return false
            }
            if (this.api_desc == '') {
                this.$error({ title: '非法的API描述', content: '不可以为空）...' })
                return false
            }
            this.edit_visible = false
            this.$message.loading('更新中...',0)
            this.$http.post(UPDATE_API, params).then((res) => {
                this.$message.destroy()//关闭loading
                if (res.data.status == 200) {                    
                    this.$success({ title: '更新API成功!' })
                    this.fetch(this.game_id)
                }else{
                    this.$message.error('更新API失败!',2)
                }
            });
        },
        handleCancel() {
            this.visible = false
            this.edit_visible = false
            this.$message.destroy()
        },
        getGameName(game_id) {
            this.$http.get(GET_GAME_NAME+'&id='+game_id).then((res) => {
                if (res.data.status == 200) {
                    this.edit_game_name = res.data.name
                }else{
                    this.$error({ title: '非法的项目ID', content: '项目ID不存在或未注册...' })
                }
            });
        }
    },
    mounted() {
        this.getGameName(this.game_id)
        this.fetch(this.game_id)
    },
} 
</script>