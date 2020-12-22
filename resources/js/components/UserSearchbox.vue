<template>
    <div class="border fill" style="padding: 8px">
        <input
            class="search-bar"
            type="text"
            placeholder="搜尋"
            v-model.lazy="search_text"
            @change="searchUser"/>
        <div class="devider"></div>
        <div class="border search-box">
            <div
                class="member-cell"
                v-for="user in searchList"
                v-bind:key="user.id"
                @click="clickUser(user.id)">
                {{ user.name }} {{user.email}} <span :class="(user.valid==1)?'green--text':'red--text'">{{(user.valid==1)?'有效':'無效'}}</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    created(){

    },
    data() {
        return {
            searchList: [],
            search_text: "",
        };
    },
    methods:{
        clickUser(user_id){
            this.$emit('clickUser',user_id);
        },
        searchUser() {
            if(!this.search_text){return;}
            axios.get("/api/search-member", {
                params: {
                    searchColumn: "name",
                    searchText: this.search_text,
                },
            })
            .then((res) => {
                this.searchList = res.data;
            })
            .catch((error) => {
                Exception.handle(error);
            });
        },
    }
}
</script>

<style>

</style>