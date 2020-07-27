<template>
  <div>
        <div style="display:inline-block">
            <v-btn :disabled="(page==1)?true:false" @click="navToPage(page-1)"><</v-btn>
            <span>{{page}} / {{totalPage}}</span>
            <v-btn :disabled="(page==totalPage)?true:false" @click="navToPage(page+1)">></v-btn>
        </div>
        <div style="display:inline-block">{{this.rowsText}}共{{this.total}}筆</div>
    </div>
</template>

<script>
export default {
    props:['total','page','rows'],
    data(){
        return{
            totalPage: 1,
            rowsText: '',
        }
    },
    watch:{
        total(){
            this.updateTotalPage();
        },
        rows(){
            this.updateTotalPage();
        },
        page(){
            this.updateTotalPage();
        }
    },
    methods:{
        updateTotalPage() {
            this.totalPage = Math.ceil(this.total / this.rows);
            this.rowsText = '';
            if (this.totalPage != 0) {
                let from = (this.page - 1) * (this.rows) + 1;
                let to = (this.rows * this.page);
                if (to > this.total) {
                    to = this.total;
                }
                this.rowsText = from + '~' + to + ',';
            }
        },
        navToPage(page) {
            this.$emit('nav_to_page', page);
        }
    }
}
</script>

<style>

</style>