<template>

  <div>
      <location-detail-modal v-on:completion="getDataList"></location-detail-modal>
      <LocationManagerModal :getUrl="'/api/getLocationManagers/'" :postUrl="'/api/addManager/'" :deleteUrl="'/api/removeManager/'"></LocationManagerModal>
      <ProductSelector></ProductSelector>
      <InventoryPanel></InventoryPanel>

      <div>
        <v-btn color="success" @click="addNewLocation">新增據點</v-btn>
      </div>

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
                <td class="manager-column">
                    <v-icon @click="openManagerPanel(props.item)">account_circle</v-icon>
                </td>
                <td>
                    <v-btn @click="openPanel(props.item.slug)">開啟後台</v-btn>
                    <v-btn color="info" @click="editLocation(props.item)">編輯</v-btn>
                    <v-btn color="success" @click="manageInventory(props.item)">庫存管理</v-btn>
                </td>
            </template>

        </v-data-table>
    </div>
  </div>

</template>

<script>
import LocationDetailModal from "./LocationDetailModal";
import LocationManagerModal from "./LocationManagerModal";
import ProductSelector from "../Product/ProductSelector";
import InventoryPanel from "./InventoryPanel";

export default {
    components:{
        LocationDetailModal,
        LocationManagerModal,
        ProductSelector,
        InventoryPanel,
    },
    data(){
        return{
            headers: [
                { text:'#'},
                { text: "據點", value: "name" },
                { text: "地址", value: "address" },
                { text: "地圖連結", value: "link" },
                { text: '後台連結'},
                { text: '負責人員'},
                { text: '-'}
            ],
            pagination: { sortBy: "id", descending: true },
            total:0,
            loading: true,
            dataList:[],
            location:'',
            inventory_location_id:null,
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
        this.location = window.location.origin;
        User.authOnly();
    },
    mounted(){
        EventBus.$on('selectProduct',product => {
            this.showInventoryPanel(product.id);
        });
    },
    destroyed(){
        EventBus.$off('selectProduct');
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
        editLocation(location){
            EventBus.$emit('showLocationDetail',location);
        },
        manageInventory(location){
            this.inventory_location_id = location.id;
            let url = `/api/location/${location.slug}/productList`;
            EventBus.$emit('showProductSelector',url);
        },
        showInventoryPanel(product_id){
            EventBus.$emit('showInventoryPanel',{
                'product_id':product_id,
                'location_id':this.inventory_location_id,
            });
        },
        addNewLocation(){
            EventBus.$emit('showLocationDetail');
        },
        openPanel(slug){
            let url = this.location+`/order-list/location/${slug}`
            window.open(url);
        },
        openManagerPanel(location){
            EventBus.$emit('showLocationManagers',{'name':location.name,'slug':location.slug});
        }
    }
}
</script>

<style>
.manager-column{
  cursor: pointer;
}
</style>