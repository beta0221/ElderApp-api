<template>
  <div id="pp">
    <nav aria-label="Page navigation example" >
      <ul class="pagination">
      <select name="" id="" class="form-control mr-3" v-model="pagination.rowsPerPage" @change="getProducts">
          <option value="15">15</option>
          <option value="30">30</option>
      </select>
        <li class="page-item" :class="{'disabled':pagination.page===1}">
          <a class="page-link" href="#" aria-label="Previous"
          @click.prevent="prev">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
        <li class="page-item">
          <a class="page-link" href="#">{{pagination.page}}</a>
        </li>
        <li class="page-item" :class="{'disabled':pagination.page==totalPage}">
          <a class="page-link" href="#" aria-label="Next"
          @click.prevent="next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      </ul>
     
    </nav>
  </div>
</template>

<script>
export default {
    props:["pagination",'totalPage'],
    data(){
        return{
        }
    },
    methods:{
        next(){
            //this.pagination
            this.pagination.page += 1;
            this.getProducts();
        },
        prev(){
            if(this.pagination.page <= 0){
                return false;
            }
            this.pagination.page -= 1;
            this.getProducts();
        },
        getProducts(){
            this.$emit('getProducts');
        }
    },
};
</script>