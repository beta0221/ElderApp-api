<template>
<div>
  
  <div>
    <v-btn color="info" @click="executeExpired">執行效期檢查</v-btn>

    <v-dialog
      v-model="dialog"
      max-width="290"
    >
    
      <v-card>
        <v-card-title class="headline">姓名：{{dialogName}}</v-card-title>

        <v-card-text>
          <p v-html="dialogText"></p>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="green darken-1"
            flat="flat"
            @click="dialog = false"
          >
            關閉
          </v-btn>

        </v-card-actions>
      </v-card>
    </v-dialog>
  
  </div>
  <div>


    <v-data-table
      :headers="headers"
      :items="desserts"
      :rows-per-page-items="[15,30]"
      :pagination.sync="pagination"
      :total-items="totalDesserts"
      :loading="loading"
      class="elevation-1"
    >

    
      <template v-slot:items="props" >
        <td class="text-xs-left">{{ props.item.id }}</td>
        <td class="text-xs-left name" @click="getMemberDetail(props.item.id,props.item.name)" :class="gender[props.item.gender]">{{ props.item.name }}</td>
        <td class="text-xs-left">{{ props.item.email }}</td>
        <td class="text-xs-left">{{ rank[props.item.rank] }}</td>
        <td class="text-xs-left">{{ props.item.inviter }}</td>
        <td class="text-xs-left">{{ props.item.inviter_phone }}</td>
        <td class="text-xs-left">{{ (props.item.created_at).substring(0,10) }}</td>
        <td class="text-xs-left history" @click="getPayHistory(props.item.id,props.item.name)">{{ props.item.last_pay_date }}</td>
        <td class="text-xs-left valid" @click="toValid(props.item.id,props.index)" :class="valid[props.item.valid].color">{{ valid[props.item.valid].text }}</td>
        <td class="text-xs-left">
          <v-btn @click="clickPayStatus(props.item.id,props.index)" :color="status[props.item.pay_status].color">{{status[props.item.pay_status].text}}</v-btn>
        </td>
      </template>
    </v-data-table>

    </div>
  </div>
</template>


<script>
  export default {
    data () {
      return {
        dialog: false,
        dialogName:'',
        dialogText:'',
        

        totalDesserts: 0,
        desserts: [],
        loading: true,
        pagination: {},
        
        gender:{1:'blue--text',0:'red--text'},
        valid:{0:{text:'無效',color:'red--text'},1:{text:'有效',color:'green--text'},},
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
          { text: '#', value: 'id' },
          { text: '姓名', value: 'name' },
          { text: '信箱', value: 'email' },
          { text: '位階', value: 'rank' },
          { text: '推薦人', value: 'inviter' },
          { text: '推薦人電話', value: 'inviter_phone' },
          { text: '入會日期', value: 'created_at' },
          { text: '上次付款日', value: 'last_pay_date' },
          { text: '效期', value: 'valid' },
          { text: '會員狀態', value: 'pay_status' },
        ],
      }
    },
    watch: {
      pagination: {
        handler () {
          this.getDataFromApi()
            .then(data => {
              this.desserts = data.items
              this.totalDesserts = data.total
            })
        },
        deep: true
      }
    },
    // created () {
      // this.getDataFromApi()
      //   .then(data => {
      //     this.desserts = data.items
      //     this.totalDesserts = data.total
      //   })
    // },
    methods: {
      customFilter(items, search, filter) {
          console.log({'items':items,'search':search,'filter':filter});
            search = search.toString().toLowerCase()
            return items.filter(row => filter(row["type"], search));

        },
      toValid(id,index){
        if(this.desserts[index]['valid'] == 0 && this.desserts[index]['last_pay_date'] != null){
          
          axios.post('/api/toValid', {
            id:id,
          })
          .then(res=>{
            if(res.data.s == 1){
              this.desserts[index]['valid'] = 1;
              this.desserts[index]['last_pay_date'] = res.data.d;
            }
            console.log(res);
          })
          .catch(error=>{
            console.log(error);
          })
        }
      },
      getMemberDetail(id,name){
        this.dialog = true;
        axios.get(`/api/getMemberDetail/${id}`)
          .then(res=>{

            if(res.data.length!=0){
              
              let text = "";
              text+='紅包餘額：'+res.data.wallet+'<br>';
              text+='地址：'+res.data.address+'<br>';
              text+='地區：'+res.data.district_id+'<br>';
              text+='緊急聯絡人：'+res.data.emg_contact+'<br>';
              text+='緊急聯絡電話：'+res.data.emg_phone+'<br>';

              this.dialogText = text;  
            }
            this.dialogName = name;

            console.log(res);
          })
          .catch(error=>{
            console.log(error);
          })
      },
      getPayHistory(id,name){
        this.dialog = true;
        axios.get(`/api/getPayHistory/${id}`)
          .then(res=>{
            
            this.dialogName = name;

            if(res.data.length!=0){
              let text = '';
              for (let i in res.data) {
                text += (res.data[i]['created_at']).substring(0,10)+'<br>';
                // console.log(res.data[i]['created_at']);
              }
              this.dialogText = text;  
            }else{
              this.dialogText = '';  
            }
            

            // console.log(res);
          })
          .catch(error=>{
            console.log(error);
          })
      },
      executeExpired(){
        axios.post('/api/executeExpired', {

          })
          .then(res=>{
            if(res.data.s==1){
              this.getDataFromApi()
              .then(data => {
                this.desserts = data.items
                this.totalDesserts = data.total
              })
            }else{
              alert('系統錯誤，未正常運作。');
            }
          })
          .catch(error=>{
            console.log(error);
          })
      },
      clickPayStatus(id,index){

        if(this.desserts[index]['pay_status'] < 3){

          axios.post('/api/changePayStatus', {
            id:id,
          })
          .then(res=>{
            // console.log(res);
            if(res.data.s == 1){
              this.desserts[index]['pay_status']++;
            }

            if(this.desserts[index]['pay_status'] == 3){
              this.desserts[index]['last_pay_date'] = res.data.d;
              this.desserts[index]['valid'] = 1;
            }
          })
          .catch(error=>{
            console.log(error);
          })

        }
      },
      getDataFromApi () {
        this.loading = true
        return new Promise((resolve, reject) => {
          const { sortBy, descending, page, rowsPerPage } = this.pagination

          // console.log(this.pagination);
          axios.get('/api/get-members',{
            params:{
              'page':this.pagination.page,
              'rowsPerPage':this.pagination.rowsPerPage,
              'descending':this.pagination.descending,
              'sortBy':this.pagination.sortBy,
            }
          })
          .then(res=> {
            // console.log(res.data.users);
            // console.log(res.data.total);
            let items =  res.data.users;
          // const total = items.length
          const total = res.data.total;

          if (this.pagination.sortBy) {
            items = items.sort((a, b) => {
              const sortA = a[sortBy]
              const sortB = b[sortBy]

              if (descending) {
                if (sortA < sortB) return 1
                if (sortA > sortB) return -1
                return 0
              } else {
                if (sortA < sortB) return -1
                if (sortA > sortB) return 1
                return 0
              }
            })
          }

          setTimeout(() => {
            this.loading = false
            resolve({
              items,
              total
            })
          }, 300)

          })
          .catch(function (error) {
            console.log(error);
          })

        })
      },
    }
  }
</script>

<style>
.history,.valid,.name{
  cursor:pointer;
}
.history:hover,.valid:hover,.name:hover{
  background-color: lightgrey;
  color:#fff;
}
</style>
