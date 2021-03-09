<template>

    <v-dialog v-model="dialog" max-width="480px">

        <v-card>
            <v-card-title class="headline">活動：{{dialogName}}</v-card-title>

            <span style="padding:2px 16px" v-if="eventGuests.length == 0">目前無人參加此活動。</span>
            <div style="padding:2px 16px" v-for="guest in eventGuests" v-bind:key="guest.id">
                <span :class="gender[guest.gender]">{{guest.name}}</span>
                <span>手機:{{guest.phone}}</span>
                <span>家電:{{guest.tel}}</span>
            </div>

            <v-card-actions>
            <v-spacer></v-spacer>
                <v-btn color="green darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
            </v-card-actions>

        </v-card>

    </v-dialog>

</template>

<script>
export default {
    mounted() {
        EventBus.$on("showEventMemberModal", (data) => {
            this.dialog = true;
            this.dialogName = data.event_name;
            this.getEventMembers(data.event_slug);
        });
    },
    destroyed(){
        EventBus.$off('showEventMemberModal');
    },
    data(){
        return{
            dialog: false,
            dialogName: "",
            eventGuests:[],
        }
    },
    methods:{
        getEventMembers(slug){
            axios.get(`/api/eventguests/${slug}`)
            .then(res => {
                if(res.data.s == 1){
                    this.eventGuests = res.data.guests;
                }
            })
            .catch(error => { Exception.handle(error); });
        }
    }
}
</script>

<style>

</style>