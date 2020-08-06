<template>

  <div>



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
                    
                </td>
                <td>

                </td>
                <td>

                </td>
                <td>
                    <v-btn color="info" >編輯</v-btn>
                </td>
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
                { text: "據點", value: "user_id" },
                { text: "地址", value: "tran_id" },
                { text: "地圖連結", value: "event" },
                { text: '-'}
            ],
            pagination: { sortBy: "id", descending: true },
            total:0,
            loading: true,
            dataList:[],
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
        User.authOnly();
        this.getDataList();
    },
    methods:{
        getDataList(){
            this.loading = true;
            axios.get('/api/', {
                params: {
                    page: this.pagination.page,
                    rowsPerPage: this.pagination.rowsPerPage,
                    descending: this.pagination.descending,
                    sortBy: this.pagination.sortBy,
                }
            })
            .catch(error => {Exception.handle(error);})
            .then(res => {
                
            })  
        },
    }
}
</script>

<style>

</style>