<template>
    <div class="com-item">
        <a-input-search style="width: 200px;" v-model="keyword"/>
        <a-button type="danger"><span>正在编辑【<span style="color: #67C23A;">{{user_name}}</span>】在【<span style="color: #409EFF;">{{edit_game_name}}</span>】里的API权限...</span></a-button>
        <a-tooltip placement="rightBottom">
            <template slot="title">
                <span>必须成功注册API后，才可访问资源!</span>
            </template>
        </a-tooltip>
        <a-alert :message="alter.message" :type="alter.type" showIcon closable style="margin: 12px 0;"/>
        <a-table
            :columns="columns"
            :dataSource="table_data"
            :pagination="pagination"
            :loading="loading">
            <span slot="game" slot-scope="text, record">{{edit_game_name}}</span>
            <span slot="action" slot-scope="text, record">
                <a-switch v-model="record.status" @change='onChange($event,record.id)'>
                  <a-icon type="check" slot="checkedChildren"/>
                  <a-icon type="close" slot="unCheckedChildren"/>
              </a-switch>
            </span>
        </a-table>
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
const API               = 'index.php?a=Api&m=getAdminApi'
const GET_GAME_NAME     = 'index.php?a=Api&m=getGameName'
const ACCESS_CHANGE     = 'index.php?a=Api&m=changeApiAccess'
export default{
    data () {
        return {
            user_name:'',
            game_id:'',
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
                    return v.api_name.indexOf(this.keyword) != -1
                })
                return arr
            }             
        }
    },
    methods:{
        onChange(checked,api_id){
            console.log(api_id,checked)
            let params = {
                api_id:api_id,
                user_name:this.user_name,
                type:checked
            }
            this.$http.post(ACCESS_CHANGE,params).then((res) => {
                if (res.data.status != 200) {
                    this.$error({  title: '操作失败!', content: '失败原因是:' + res.data.msg })
                }
            })
        },
        fetch(user_name, game_id) {
            this.$http.get(API+'&user_name='+user_name+'&game_id='+game_id).then((res) => {
                this.loading = false
                if (res.data.status == 200) {
                    this.data = res.data.data;
                    this.alter = {message:'加载成功!', type:'success'}
                }else{
                    this.alter = {message:res.data.msg, type:'error'}
                }                    
            })
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
        this.game_id = this.$route.params.game_id
        this.user_name = this.$route.params.user_name
        this.getGameName(this.game_id)
        this.fetch(this.user_name, this.game_id)
    },
} 
</script>