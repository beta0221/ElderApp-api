<template>
  <div style="padding:12px 24px">


    <v-layout row wrap>

        <v-flex md6>
            <h2>總流通樂幣：<font color="orange">{{dashboardData.totalWallet}}</font></h2>
            <h2>總會員數：{{dashboardData.totalSum}} 人</h2>
            <h2>有效會員數：<font color="green">{{dashboardData.validSum}}</font> 人</h2>
            <h2>無效會員數：<font color="red">{{dashboardData.unValidSum}}</font> 人</h2>

            <h2>年齡區間</h2>
            <div style="padding-left:24px" v-for="(value,key) in dashboardData.ageDist" v-bind:key="key">
                <h4><span>{{key}} 歲</span>：<span>{{value}} 人</span></h4>
            </div>

            
        </v-flex>

        <v-flex md6>
            
            <h2>今日壽星名單</h2>
            <div style="padding-left:24px" v-for="(name,index) in dashboardData.birthdayList" v-bind:key="index">
                <h4>{{name}}</h4>
            </div>
            <h2>職位人數</h2>
            <div style="padding-left:24px">
                <h4><span>領航天使</span>：<span>{{dashboardData.orgRankSum5}} 人</span></h4>
                <h4><span>守護天使</span>：<span>{{dashboardData.orgRankSum4}} 人</span></h4>
                <h4><span>大天使</span>：<span>{{dashboardData.orgRankSum3}} 人</span></h4>
                <h4><span>小天使</span>：<span>{{dashboardData.orgRankSum2}} 人</span></h4>
                <h4><span>主人</span>：<span>{{dashboardData.orgRankSum1}} 人</span></h4>
            </div>
            

            <h2>各區人數分布</h2>
            <div style="padding-left:24px" v-for="(value,key) in dashboardData.districtSum" v-bind:key="key">
                <h4><span>{{districtDict[key]}} 區</span>：<span>{{value}} 人</span></h4>
            </div>
            
        </v-flex>

    </v-layout>

    <div>
        <hr>
        <div>
            <v-btn v-if="groupBtn" color="info" @click="getGroupleaders">載入組織狀態</v-btn>
        </div>
        <div>
            <div v-for="(status,index) in groupStatusList" v-bind:key="index">
                <h3>{{status.name}} ({{status.total}})</h3>
                <div style="padding-left:12px">
                    <span>有效：<font color="green">{{status.valid}}</font></span><br>
                    <span>無效：<font color="red">{{status.unValid}}</font></span><br>
                </div>
            </div>
        </div>
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
                {url:'getTotalWallet',param:'totalWallet'},
                {url:'getBirthdayList',param:'birthdayList'},
                {url:'getOrgRankSum5',param:'orgRankSum5'},
                {url:'getOrgRankSum4',param:'orgRankSum4'},
                {url:'getOrgRankSum3',param:'orgRankSum3'},
                {url:'getOrgRankSum2',param:'orgRankSum2'},
                {url:'getOrgRankSum1',param:'orgRankSum1'},
                {url:'getDistrictSum',param:'districtSum'},
            ],
            districtDict:{
                '1': "桃園",
                '2': "中壢",
                '3': "平鎮",
                '4': "八德",
                '5': "龜山",
                '6': "蘆竹",
                '7': "大園",
                '8': "觀音",
                '9': "新屋",
                '10': "楊梅",
                '11': "龍潭",
                '12': "大溪",
                '13': "復興",
            },
            dashboardData:{
                totalSum:0,
                validSum:0,
                unValidSum:0,
                ageDist:{
                    '50-55':0,
                    '55-60':0,
                    '60-65':0,
                    '65-100':0,
                },
                totalWallet:0,
                birthdayList:[],
                orgRankSum5:0,
                orgRankSum4:0,
                orgRankSum3:0,
                orgRankSum2:0,
                orgRankSum1:0,
                districtSum:{},
            },
            groupStatusList:[],
            groupBtn:true,
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
        getGroupleaders(){
            this.groupBtn = false;
            axios.get('/api/dashboard/getGroupleaders')
            .catch(err => {Exception.handle(error);})
            .then(res => {
                let leader_id_array = res.data;
                let delay = 0;
                leader_id_array.forEach(leader_id => {
                    setTimeout(()=>{
                        this.getGroupStatus(leader_id);
                    }, delay);
                    delay += 800;
                });
            })
        },
        getGroupStatus(leader_id){
            axios.get('/api/dashboard/getGroupStatus', {
                params: {
                    leader_id: leader_id
                }
            })
            .catch(err => {Exception.handle(error);})
            .then(res => {
                this.groupStatusList.push(res.data);
            })
        }
    }

}
</script>

<style>
h2{
    margin: 16px 0;
}
</style>