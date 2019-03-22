<template>
    <a-locale-provider :locale="zh_CN">
    <div id="app">
        <router-view />
    </div>
    </a-locale-provider>
</template>

<script>
    import zh_CN from 'ant-design-vue/lib/locale-provider/zh_CN';
    export default {
        name: 'App',
        data() {
            return {
              zh_CN
          }
      },
      mounted(){
        document.getElementById('app').style.display = 'block';
        document.getElementById('appLoading').style.display = 'none';
      }
}
</script>

<style>
#app {
    font-family: 'Avenir', Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    height:100%;
}
.com-item{
    background-color: white;
    border:1px solid #DCDFE6;
    box-shadow: 0 1px 4px rgba(0,21,41,.08);
    border-radius: 6px;
    padding: 16px;
}
.table{
    margin-top: 0.5rem;
}
</style>
