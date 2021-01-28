<template>
    <v-dialog v-model="dialog" max-width="480px">
        
        <v-card>
            <v-card-title class="headline">輸出報表</v-card-title>
            
            <div style="padding:8px">
                <label for="">據點</label>
                <v-select v-model="location_slug" :items="locations" item-text="name" item-value="slug" @change="selectLocation"></v-select>
                
                <label for="">產品</label>
                <v-select v-model="product_id" :items="products" item-text="name" item-value="id" ></v-select>

                <label for="">日期區間</label><br>
                <input type="date" v-model="from_date">-<input type="date" v-model="to_date">
            </div>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="exportExcel" color="green darken-1">輸出</v-btn>
                <v-btn @click="clearQuery">清除</v-btn>
                <v-btn color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
            </v-card-actions>

        </v-card>

    </v-dialog>
</template>

<script>


export default {
    components:{
        
    },
    mounted() {
        EventBus.$on("showOrderQueryPanel", () => {
            this.getLocations();
            this.dialog = true;
        });
    },
    destroyed(){
        EventBus.$off('showOrderQueryPanel');
    },
    data() {
        return {
            dialog: false,
            locations:[],
            location_slug:null,
            products:[],
            product_id:null,
            from_date:null,
            to_date:null,
        };
    },
    methods:{
        getLocations(){
            axios.get('/api/location')
            .catch(error => {Exception.handle(error);})
            .then( res => {
                this.locations = res.data;
            })
        },
        selectLocation(){
            let url = `/api/location/${this.location_slug}/productList`;
            this.product_id = null;
            axios.get(url)
            .catch(error => {Exception.handle(error);})
            .then( res => {
                this.products = res.data;
            })
        },
        exportExcel(){
            if(!this.location_slug || !this.from_date || !this.to_date){ alert('請選擇據點及日期');return; }
            let openUrl = new URL(window.location.href);
            //openUrl.host = window.host;
            openUrl.pathname = '/order/locationOrderExcel';
            openUrl.searchParams.set('location_slug',this.location_slug);
            openUrl.searchParams.set('from_date',this.from_date);
            openUrl.searchParams.set('to_date',this.to_date);
            if(this.product_id){
                openUrl.searchParams.set('product_id',this.product_id);
            }
            window.open(openUrl.toString());
        },
        clearQuery(){
            this.location_slug = null;
            this.from_date = null;
            this.to_date = null;
            this.product_id = null;
        }
    }
}
</script>

<style>

</style>