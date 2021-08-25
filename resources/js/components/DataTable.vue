<template>
  <div>
    <v-data-table
        :headers="headers"
        :items="items"
        :rows-per-page-items="[15,30]"
        :pagination.sync="pagination"
        :total-items="total"
        :loading="loading"
        class="elevation-1">

        <template v-slot:items="props">
            <td v-for="header in headers" v-bind:key="header.text">
                <span v-if="header.text == '#'">{{props.index + 1}}</span>
                <span v-if="header.text == '顯示'">
                    <v-btn @click="showDetail(props.item)">顯示</v-btn>
                </span>
                <span v-if="header.text == '勾選'">
                    <input type="checkbox" v-model="props.item.isCheck">
                </span>

                <span v-if="header.text == '-'">
                    <v-btn @click="clickEventButton(props.index,header.eventName,header.eventParam)">
                        {{header.btnName}}
                    </v-btn>
                </span>

                <div v-else>
                    <span v-if="dict[header.value] != undefined">
                        <span :style="'color:'+ dict[header.value]['color'][props.item[header.value]]">
                            {{dict[header.value]['value'][props.item[header.value]]}}
                        </span>    
                    </span>
                    <span v-else>{{props.item[header.value]}}</span>
                </div>
                
            </td>    
        </template>
        

    </v-data-table>
  </div>
</template>

<script>
export default {
    props: ["headers", "requestUrl","dict"],
    data() {
        return {
            filter:{},
            items: [],
            pagination: { sortBy: "id", descending: true ,rowsPerPage:15},
            loading: true,
            total:0,
            //
            isSelectAll:false,
        };
    },
    created() {
        // this.reloadData();
    },
    mounted() {
        EventBus.$on("reloadData", (_) => {
            this.reloadData();
        });
        EventBus.$on("reloadDataWithFilter",filter =>{
            this.filter = filter;
            this.reloadData();
        });
    },
    destroyed() {
        EventBus.$off("reloadData");
        EventBus.$off("reloadDataWithFilter");
    },
    watch:{
        pagination: {
            handler(){
                this.reloadData();
            }
        }
    },
    methods: {
        setRows(value) {
            this.pagination.rows = value;
            this.reloadData();
        },
        reloadData() {
            this.isSelectAll = false;
            this.loading = true;
            let _params = Object.assign({},this.pagination);
            Object.keys(this.filter).forEach(key => {
            let value = this.filter[key];
                if(value != null || value != ''){
                    _params[key] = value;
                }
            });
            axios
            .get(this.requestUrl, {
                params: _params,
            })
            .then((res) => {
                this.loading = false;
                this.items = res.data.items;
                this.total = res.data.total;
            })
            .catch((error) => {
                this.loading = false;
                Exception.handle(error);
            });
        },
        showDetail(item) {
            EventBus.$emit("showDetailModal", item);
        },
        deleteItem(item){
            axios.post(this.requestUrl + "/" + item.id, {
                '_method':'DELETE'
            })
            .then(res => {
                this.reloadData();
            })
            .catch(error => {
                Exception.handle(error);
            })
        },
        getSelectedArray(){
            let selectedArray = [];
            this.items.forEach((item)=>{
                if(item.isCheck != true){ return; }
                if(item.id != undefined){
                    selectedArray.push(item.id);
                }
            });
            return selectedArray;
        },
        selectAll(){
            this.isSelectAll = !this.isSelectAll;
            this.items.forEach((item,i)=>{
                this.$set(this.items[i],'isCheck',this.isSelectAll);
            });
        },
        clickEventButton(index,eventName,eventParam){
            let param = {};
            eventParam.forEach(column=>{
                param[column] = this.items[index][column];
            });
            EventBus.$emit(eventName,param);
        }
    },
};
</script>

<style>
</style>