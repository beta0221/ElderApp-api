<template>
  <v-data-table
    :headers="headers"
    :items="desserts"
    :rows-per-page-items="[15,30]"
    class="elevation-1"
  >
    <template v-slot:items="props" >
      <td class="text-xs-left" :class="gender[props.item.gender]">{{ props.item.name }}</td>
      <td class="text-xs-left">{{ props.item.email }}</td>
      <td class="text-xs-left">{{ rank[props.item.rank] }}</td>
      <td class="text-xs-left">{{ props.item.inviter }}</td>
      <td class="text-xs-left">{{ props.item.inviter_phone }}</td>
      <td class="text-xs-left">{{ props.item.join_date }}</td>
      <td class="text-xs-left">{{ props.item.last_pay_date }}</td>
      <td class="text-xs-left">
        <v-btn :color="status[props.item.pay_status].color">{{status[props.item.pay_status].text}}</v-btn>
      </td>
      
      
    </template>
  </v-data-table>
</template>


<script>
  export default {
    data () {
      return {
        gender:{1:'blue--text',0:'red--text'},
        status:{
          '0':{text:'免費',color:'grey'},
          '1':{text:'申請中',color:'warning'},
          '2':{text:'已繳清',color:'info'},
          '3':{text:'完成',color:'success'},
        },
        rank:{
          '1':'',
          '2':'組長',
          '3':'理事',
        },
        headers: [
          { text: '姓名', value: 'name' },
          { text: '信箱', value: 'email' },
          { text: '位階', value: 'rank' },
          { text: '推薦人', value: 'inviter' },
          { text: '推薦人電話', value: 'inviter_phone' },
          { text: '入會日期', value: 'join_date' },
          { text: '上次付款日', value: 'last_pay_date' },
          { text: '會員狀態', value: 'pay_status' },
        ],
        desserts: [

        ],
      }
    },
    methods:{
      getMembers(){
        axios.get('/api/get-members')
        .then(res=> {
          this.desserts = res.data;
          console.log(res.data);
        })
        .catch(function (error) {
          console.log(error);
        })
      },
    },
    created(){
      this.getMembers();
    }
  }
</script>

<style>

</style>
