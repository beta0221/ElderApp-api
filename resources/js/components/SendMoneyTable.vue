<template>
<div style="padding:80px">
    <v-textarea solo
    name="input-7-4"
    v-model="textContent"
    label="會員帳號(以逗點隔開)(A123123,B456789,C093828)"></v-textarea>
    <v-text-field label="Solo" placeholder="事件" solo v-model="request.event"></v-text-field>
    <v-text-field label="Solo" placeholder="樂幣" solo v-model="request.amount"></v-text-field>
    <v-btn style="margin-bottom:24px" block color="info" @click="sendRequest">發送</v-btn>
    <v-textarea solo
    name="input-7-4"
    v-model="notFoundContent"
    label="發送錯誤結果"></v-textarea>
</div>
  
</template>

<script>
export default {
    data(){
        return{
            textContent:'',
            request:{
                'account_array':'',
                'event':null,
                'amount':null,
            },
            notFoundContent:''
        }
    },
    created(){
        User.authOnly();
    },
    methods:{
        sendRequest(){
            
            let textContent = this.textContent.replace(/\s/g, "")
            let account_array = textContent.split(",");
            this.request.account_array = JSON.stringify(account_array);
            this.notFoundContent = '';

            axios.post('/api/sendMoneyToUsers',this.request)
            .then(res => {
                if(res.data.s ==1){
                    this.textContent = '';
                    if(res.data.not_found_array.length == 0){
                        alert('全部成功');
                    }else{
                        let not_found_alert = '無搜尋結果：\n';
                        let not_found_string = '';
                        res.data.not_found_array.forEach(element => {
                            not_found_string += (element+'\n');
                            not_found_alert += (element+'\n');
                        });
                        this.notFoundContent = not_found_string;
                        alert(not_found_alert);
                    }
                }else{
                    alert(res.data.m);
                }
            })
            .catch(err => {
                alert('錯誤');
            })
        }    
    }
}
</script>

<style>

</style>