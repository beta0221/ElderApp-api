<template>
  <div style="padding:24px">
        <h2>總會員數：{{dashboardData.totalSum}}</h2>
        <h2>有效會員數：<font color="green">{{dashboardData.validSum}}</font></h2>
        <h2>無效會員數：<font color="red">{{dashboardData.unValidSum}}</font></h2>

        <h2>年齡區間</h2>
        <div style="padding-left:24px" v-for="(value,key) in dashboardData.ageDist" v-bind:key="key">
            <h4><span>{{key}}</span>：<span>{{value}}</span></h4>
        </div>
       
  </div>
  
</template>

<script>
export default {
    data(){
        return{
            requestList: [
                {url:'getTotal',param:'totalSum'},
                {url:'getValid',param:'validSum'},
                {url:'getUnValid',param:'unValidSum'},
                {url:'getAgeDist',param:'ageDist'},
            ],
            dashboardData:{
                totalSum:0,
                validSum:0,
                unValidSum:0,
                ageDist:{
                    '50-55':0,
                    '55-60':0,
                    '60-65':0,
                    '65-100':0,
                }
            },
        }
    },
    created(){
        User.authOnly();
        this.loadDashboard();
    },
    methods:{
        loadDashboard(){
            let delay = 0;
            this.requestList.forEach(request=>{
                setTimeout(()=>{
                    this.sendRequest(request);
                }, delay);
                delay += 500;
            });
        },
        sendRequest(request){
            axios.get(`/api/dashboard/${request.url}`)
            .then(res =>{
                this.dashboardData[request.param] = res.data;
            })
            .catch(error => {
                Exception.handle(error);
            })
        },
    }

}
</script>

<style>

</style>