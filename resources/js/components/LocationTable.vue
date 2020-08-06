<template>

  <div>

      <!-- <div>
        <v-btn color="success">新增據點</v-btn>
      </div> -->

      <div>
            <v-data-table
            :headers="headers"
            :items="dataList"
            :rows-per-page-items="[15,30]"
            :pagination.sync="pagination"
            :total-items="total"
            :loading="loading"
            class="elevation-1">

            <template v-slot:items="props">
                <td>{{props.index + 1}}</td>
                <td>
                    {{props.item.name}}
                </td>
                <td>
                    {{props.item.address}}
                </td>
                <td>
                    {{props.item.link}}
                </td>
                <td>
                    {{location+`/order-list/location/${props.item.slug}`}}
                </td>
                <!-- <td>
                    <v-btn color="info" >編輯</v-btn>
                </td> -->
            </template>

        </v-data-table>
    </div>
  </div>

</template>

<script>
export default {
data(){
        return{
            headers: [
                { text:'#'},
                { text: "據點", value: "name" },
                { text: "地址", value: "address" },
                { text: "地圖連結", value: "link" },
                { text: '後台連結'},
                // { text: '-'}
            ],
            pagination: { sortBy: "id", descending: true },
            total:0,
            loading: true,
            dataList:[],
            location:'',
        }
    },
    watch:{
        pagination: {
            handler(){
                this.getDataList();
            }
        }
    },
    created(){
        this.location = window.location;
        User.authOnly();
        // this.getDataList();
    },
    methods:{
        getDataList(){
            this.loading = true;
            axios.get('/api/locationList', {
                params: {
                    page: this.pagination.page,
                    rowsPerPage: this.pagination.rowsPerPage,
                    descending: this.pagination.descending,
                    sortBy: this.pagination.sortBy,
                }
            })
            .catch(error => {Exception.handle(error);})
            .then(res => {
                this.total = res.data.total;
                this.dataList = res.data.locationList;
                this.loading=false;
            })  
        },
    }
}
</script>

<style>

</style>