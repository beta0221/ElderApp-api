<template>
  <div>
    <div class="switch">
      <input type="checkbox" class="checkbox" 
      :true-value="1" :false-value="0" v-model="aaa"
       @change="$emit('getValue',aaa)"/>
      <div class="track"></div>
      <div class="thumb"></div>
    </div>
  </div>
</template>
<script>
export default {
  name:"CheckboxBtn",
  props:["isChecked"],
  data() {
    return {
       aaa:this.isChecked,
    };
  },
  watch:{
      isChecked(val){
          this.aaa=val;
      }
  }
};
</script>
<style scoped>
.switch {
  position: relative;
  cursor: pointer;
  display: inline-block;
  width: 54px;
  height: 32px;
}

.checkbox {
  height: 32px;
  width: 54px;
  opacity: 0;
  margin: 0;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
  cursor: pointer;
}

.track {
  position: absolute;
  top: 0;
  left: 0;
  height: 32px;
  width: 100%;
  background-color: gray;
  border-radius: 16px;
}

.thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  background-color: white;
  border-radius: 50%;
  height: 28px;
  width: 28px;
  transition: transform 0.1s ease-in-out;
}

.checkbox:checked ~ .thumb {
  transform: translateX(22px);
}

.checkbox:checked ~ .track {
  background-color: #51d86a;
}
</style>