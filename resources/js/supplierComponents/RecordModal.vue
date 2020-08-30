<template>
  <div>
    <div
      class="modal fade"
      id="recordModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="recordModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content" >
          <div class="modal-header">
            <h5 class="modal-title" id="recordModalLabel" v-if="renderLater">產品：{{products[itemKey].name}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <tr v-for="(rec,key) in record" :key="key">
                  <span v-if="rec.receive==1" class="text-success">已領取</span>
                  <span v-else class="text-danger">尚未領取</span>
                  <td>{{rec.name}}  {{rec.location}} {{rec.created_at}}</td>
              </tr>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
    props:["recordShow","record","products","itemKey"],
    data(){
        return{
           renderLater:false,
        } 
    },
    watch:{
        recordShow(){
            $("#recordModal").modal("show");
            this.renderLater=true;
        }
    }
};
</script>