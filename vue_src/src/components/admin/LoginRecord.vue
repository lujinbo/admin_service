<template>
    <div class="com-item">        
        <a-input-search style="width: 200px;" v-model="keyword"/>
        <a-alert :message="alter.message" :type="alter.type" showIcon closable style="margin: 12px 0;"/>
        <a-table
            :columns="columns"
            :dataSource="data"
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
        title: '登录后台',
        dataIndex:'game'
    },
    {
        title: '登录类型',
        dataIndex:'type'
    },
    {
        title: '登录时间',
        dataIndex:'login_time'
    },
    {
        title: '登录IP',
        dataIndex:'login_ip'
    },
    {
        title: '登录地区',
        dataIndex:'login_locate'
    }
]
const API       = 'index.php?a=Admin&m=getAdminLoginRecord'
const IP_ADDR   = 'index.php?a=Admin&m=getIpInfo&ip='
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
    methods:{
        fetch(user_name) {
            this.$http.get(API+'&user_name='+user_name).then((res) => {
                this.loading = false
                if (res.data.status == 200) {
                    this.data = res.data.data;
                    this.alter = {message:'加载成功!', type:'success'}
                    this.asyncGetIpInfo()
                }else{
                    this.alter = {message:res.data.msg, type:'error'}
                }                  
            })
        },
        asyncGetIpInfo(){
            let arr = new Set()
            this.data.forEach((v,k) => {
                arr.add(v.login_ip)
            })
            arr.forEach((v) => {
                this.getIpInfo(v)
            })
        },
        getIpInfo(ip){
            this.$http.get(IP_ADDR+ip).then((res) => {
                if (res.status == 200) {
                    this.data.forEach((v,k) => {
                        if (v.login_ip == ip) {
                            this.data[k].login_locate = res.data.addr
                        }
                    })
                    this.$forceUpdate()
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