<template>
  <v-dialog v-model="showDialog" max-width="480px">
      <v-card id="member-detail-dialog">

          <v-card-title class="headline">{{(isReadMode)?location.name:'新增據點'}}</v-card-title>

            <div class="data-row">
                <span>據點</span>
                <input type="text" v-model="location.name">
            </div>

            <div class="data-row">
                <span>地址</span>
                <input type="text" v-model="location.address">
            </div>
            
            <div class="data-row">
                <span>地圖連結</span>
                <input type="text" v-model="location.link">
            </div>
            <div style="height:40px"></div>

            <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn v-show="!isReadMode" color="success" @click="submitForm">新增</v-btn>
                    <v-btn v-show="isReadMode" color="info" @click="submitForm">確定送出</v-btn>
            </v-card-actions>

      </v-card>
  </v-dialog>
</template>

<script>
export default {
  destroyed(){
    EventBus.$off('showLocationDetail');
  },
  mounted() {
    EventBus.$on("showLocationDetail", (location) => {
      this.showDialog = true;
      this.location = {};
      this.isReadMode = false;
      if (location) {
        Object.assign(this.location,location);
        this.isReadMode = true;
      }
    });
  },
  data() {
    return {
      isReadMode: false,
      showDialog: false,
      location: {},
    };
  },
  methods: {
      submitForm(){
          if(this.isReadMode){
              this.updateRequest();
          }else{
              this.insertRequest();
          }
      },
      insertRequest(){
            axios.post('/api/insertLocation',this.location)
            .then(res => {
                this.completion();
            })
            .catch(err => {
                Exception.handle(error);
            })
      },
      updateRequest(){
            axios.post('/api/updateLocation',this.location)
            .then(res => {
                this.completion();
            })
            .catch(err => {
                Exception.handle(error);
            })
      },
      completion(){
          this.showDialog = false;
          this.$emit('completion');
      }
  },
};
</script>

<style>
</style>