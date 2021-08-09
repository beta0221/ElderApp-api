<template>
    <div>
        <div v-for="(value,key) in columns" v-bind:key="key"
            style="display:inline-block;width:160px;margin-left:20px;">

            <v-text-field v-if="typeof(value) == 'string'"
            append-icon="search"
            v-model.lazy="filter[key]"
            @keyup.native.enter="search"
            :label="value"
            single-line
            hide-details
            ></v-text-field>


            <v-select v-if="typeof(value) == 'object'"
                @change="search"
                v-model="filter[key]" 
                :items="value" 
                item-value="value" 
                :label="value[0].text"></v-select>


        </div>
    </div>
</template>

<script>
export default {
    props:['columns'],
    data(){
        return{
            searchColumn:null,
            searchValue:null,
            filter:{}
        }
    },
    methods:{
        search(){
            EventBus.$emit('reloadDataWithFilter',this.filter);
        }
    }
}
</script>

<style>

</style>