<template>
<div>

  <div>
    <v-btn color="success">新增活動</v-btn>
  </div>

  <div>
    <v-data-table
      :headers="headers"
      :items="eventArray"
      :rows-per-page-items="[15,30]"
      :pagination.sync="pagination"
      :total-items="totalEvent"
      :loading="loading"
      class="elevation-1"
    >
      <template v-slot:items="props">
        <td>{{props.index + 1}}</td>
        <td>{{eventCat[props.item.category_id]}}</td>
        <td>{{props.item.title}}</td>
        <td>{{props.item.location}}</td>
        <td>{{props.item.dateTime}}</td>
        <td>{{props.item.deadline}}</td>
      </template>
    </v-data-table>
  </div>
</div>


</template>

<script>
export default {
  data() {
    return {
      pagination: { sortBy: "id", descending: true },
      totalEvent: 0,
      loading: true,
      eventArray: [],
      eventCat:{},
      headers: [
        { text:'#'},
        { text: "類別", value: "category_id" },
        { text: "活動", value: "title" },
        { text: "地點", value: "location" },
        { text: "活動時間", value: "dateTime" },
        { text: "截止日期", value: "deadline" }
      ],
      
    };
  },
  watch: {
    pagination: {
      handler() {
        this.getDataFromApi().then(data => {
          this.eventArray = data.items;
          this.totalEvent = data.total;
        });
      }
    }
  },
  created(){
    this.getCat();
  },
  methods:{
    getCat(){
      axios.get('/api/category')
      .then(res => {
        
        for(let data of res.data){
          this.eventCat[data.id] = data.name;
        }
      })
      .catch(err => {
        console.error(err); 
      })
    },
    getDataFromApi() {
      this.loading = true;
      return new Promise((resolve, reject) => {
        const { sortBy, descending, page, rowsPerPage } = this.pagination;
        // console.log(this.pagination);
         axios
          .get("/api/event", {
            params: {
              page: this.pagination.page,
              rowsPerPage: this.pagination.rowsPerPage,
              descending: this.pagination.descending,
              sortBy: this.pagination.sortBy
            }
          })
          .then(res => {
            // console.log(res.data);
            // return false;
            let items = res.data.events;
            
            const total = res.data.total;

            if (this.pagination.sortBy) {
              items = items.sort((a, b) => {
                const sortA = a[sortBy];
                const sortB = b[sortBy];

                if (descending) {
                  if (sortA < sortB) return 1;
                  if (sortA > sortB) return -1;
                  return 0;
                } else {
                  if (sortA < sortB) return -1;
                  if (sortA > sortB) return 1;
                  return 0;
                }
              });
            }

            setTimeout(() => {
              this.loading = false;
              resolve({
                items,
                total
              });
            }, 300);
          })
          .catch(error => {
            Exception.handle(error);
            // User.logout();
          })
      });
    },

  }


};
</script>

<style>
</style>