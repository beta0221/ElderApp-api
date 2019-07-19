<template>
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
      <td class="text-xs-left" :class="gender[props.item.gender]">{{ props.item.name }}</td>
      <td class="text-xs-left">{{ props.item.email }}</td>
      <td class="text-xs-left">{{ rank[props.item.rank] }}</td>
      <td class="text-xs-left">{{ props.item.inviter }}</td>
      <td class="text-xs-left">{{ props.item.inviter_phone }}</td>
      <td class="text-xs-left">{{ props.item.join_date }}</td>
      <td class="text-xs-left">{{ props.item.last_pay_date }}</td>
      <td class="text-xs-left">
        <v-btn @click="clickPayStatus(props.item.id,props.index)" :color="status[props.item.pay_status].color">{{status[props.item.pay_status].text}}</v-btn>
      </td>
    </template>
  </v-data-table>
</template>


<script>
  export default {
    data () {
      return {
        totalDesserts: 0,
        desserts: [],
        loading: true,
        pagination: {},
        

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
          { text: '#', value: 'id' },
          { text: '姓名', value: 'name' },
          { text: '信箱', value: 'email' },
          { text: '位階', value: 'rank' },
          { text: '推薦人', value: 'inviter' },
          { text: '推薦人電話', value: 'inviter_phone' },
          { text: '入會日期', value: 'join_date' },
          { text: '上次付款日', value: 'last_pay_date' },
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
      clickPayStatus(id,index){

        axios.post('/api/changePayStatus', {
          id:id,
        })
        .then(res=>{
          console.log(res);
          if(res.data.s == 1){
            if(this.desserts[index]['pay_status'] == 3){
              this.desserts[index]['pay_status'] = 0;
            }else{
              this.desserts[index]['pay_status']++;
            }
            
          }
        })
        .catch(error=>{
          console.log(error);
        })
      },
      getDataFromApi () {
        this.loading = true
        return new Promise((resolve, reject) => {
          const { sortBy, descending, page, rowsPerPage } = this.pagination

          console.log(this.pagination);
          axios.get('/api/get-members',{
            params:{
              'page':this.pagination.page,
              'rowsPerPage':this.pagination.rowsPerPage,
              'descending':this.pagination.descending,
              'sortBy':this.pagination.sortBy,
            }
          })
          .then(res=> {
            console.log(res.data.users);
            console.log(res.data.total);
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

</style>
