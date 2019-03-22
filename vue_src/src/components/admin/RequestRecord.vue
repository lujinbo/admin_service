<template>
    <div class="com-item">        
        <a-input-search style="width: 200px;" v-model="keyword"/>
        <a-alert :message="alter.message" :type="alter.type" showIcon closable style="margin: 12px 0;"/>
        <a-table
            :columns="columns"
            :dataSource="table_data"
            :pagination="pagination"
            :loading="loading">
        </a-table>
    </div>
</template>
<script>
const columns = [
    {
        title: '管理员账号',
        dataIndex:'user_name'
    },
    {
        title: '请求后台',
        dataIndex:'game'
    },
    {
        title: '请求API',
        dataIndex:'api'
    },
    {
        title: '请求时间',
        dataIndex:'r_time'
    },
    {
        title: '请求IP',
        dataIndex:'r_ip'
    },
]
const API       = 'index.php?a=Admin&m=getAdminRequestRecord'
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
            keyword:''
        }
    },
    computed:{
        table_data(){
            if (this.keyword == '') {
                return this.data
            }else{
                let arr = this.data.filter((v) => {
                    return v.user_name.indexOf(this.keyword) != -1 || v.api.indexOf(this.keyword) != -1 || v.game.indexOf(this.keyword) != -1 || v.r_ip.indexOf(this.keyword) != -1
                })
                return arr
            } 
        }
    },
    methods:{
        fetch(user_name) {
            this.$http.get(API+'&user_name='+user_name).then((res) => {
                this.loading = false
                if (res.data.status == 200) {
                    this.data = res.data.data;
                    this.alter = {message:'加载成功!', type:'success'}
                }else{
                    this.alter = {message:res.data.msg, type:'error'}
                }                  
            })
        }
    },
    mounted() {
        let user_name = this.$route.params.user_name
        this.fetch(user_name)
    },
} 
</script>