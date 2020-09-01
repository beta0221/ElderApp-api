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
        <div class="modal-content">
          <div class="modal-header">
            <h5
              class="modal-title"
              id="recordModalLabel"
              v-if="renderLater"
            >產品：{{products[itemKey].name}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" >
            <table class="table">
            <tr v-for="(rec,key) in record" :key="key">
              <td class="align-middle"  style="padding:0.3rem;margin:0.3rem">
                <span v-if="rec.receive==1" class="text-success">已領取</span>
                <span v-else class="text-danger">尚未領取</span>
              </td>
              <td 
                class="align-middle"  style="padding:0.3rem;margin:0.3rem"
              >領取人 :{{rec.name}} 領取地點 :{{rec.location}} 時間 :{{rec.created_at}}</td>
            </tr>
            </table>
          </div>
          <div class="modal-footer">
            <nav aria-label="Page navigation example">
              <ul class="pagination">
                <li class="page-item" :class="{'disabled':recordWatch===1}">
                  <a class="page-link" href="#" aria-label="Previous" @click.prevent="prev">
                    <span aria-hidden="true">&laquo;</span>
                  </a>
                </li>
                <li class="page-item">
                  <a class="page-link" href="#">{{recordWatch}}</a>
                </li>
                <li class="page-item" :class="{'disabled':recordWatch==recordPageTotal}">
                  <a class="page-link" href="#" aria-label="Next" @click.prevent="next">
                    <span aria-hidden="true">&raquo;</span>
                  </a>
                </li>
              </ul>
            </nav>
            <span>共{{recordTotal}}筆</span>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
    props:["recordShow","record","products","itemKey","recordTotal",
    "recordPage","totalPage","recordPageTotal","recordId"],
    data(){
        return{
           renderLater:false,
           recordWatch:this.recordPage,
        } 
    },
    watch:{
        recordShow(){
            $("#recordModal").modal("show");
            this.renderLater=true;
        },
        recordPage(val){
            this.recordWatch=val;
        }
    },
    methods:{
      next(){
            //this.pagination
            this.recordWatch += 1;
            this.getRecord();
        },
        prev(){
            if(this.recordWatch <= 0){
                return false;
            }
            this.recordWatch -= 1;
            this.getRecord();
        },
        getRecord(){
            this.$emit('getRecord',this.recordId,this.recordWatch);
        }
    },
    }
</script>