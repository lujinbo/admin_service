<template>
    <div class="com-item">
        @todo 欢迎页/Dashboard
    </div>
</template>
<script>
const api = 'index.php?a=Admin&m=getAdminList'
export default{
    data () {
        return {
            columns:'',
        }
    },
    methods:{
        showModal() {
            //this.visible = true
        },
    },
    mounted() {
        //this.fetch();
    },
} 
</script>