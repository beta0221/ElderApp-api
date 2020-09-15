<template>
    
      
     <nav id="sidebarMenu" class="col-md-2 col-lg-2 d-md-block bg-light sidebar collapse p-0">
       
      <div class="sidebar-sticky">

        <!-- <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
          <span>管理員</span>
          <a class="d-flex align-items-center text-muted" href="#" aria-label="Add a new report">
            <span data-feather="plus-circle"></span>
          </a>
        </h6> -->
        <h5 class="px-3 m-0">廠商：{{user_name}}</h5>
        <hr>
         <ul class="nav flex-column mt-2">   
          <li class="nav-item">            
              <router-link class="nav-link"
              to="/supplier/admin/supplierProducts">
              <font-awesome-icon icon="box-open" />
              <span>商品管理</span>  </router-link>                       
          </li>
          <li class="nav-item">            
              <router-link class="nav-link"
              to="/supplier/admin/supplierOrders"> 
               <font-awesome-icon icon="list" style="width: 1.3em;" />
               <span>訂單管理</span> </router-link>                       
          </li>
        </ul>
       
      </div>
    </nav>

    <!-- <nav class="navbar navbar-expand-lg">
			<div class="container-fluid">
				<button class="navbar-toggler" type="button" data-toggle="collapse"
				data-target="#navbarResponsive">
				<span class="custom-toggler-icon"><i class="fas fa-bars"></i></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarResponsive">
					 <ul class="nav flex-column mb-2">
             <li class="nav-item">            
              <router-link class="nav-link"
              to="/supplier/supplierOrders"> 
              <i class="far fa-list-alt"></i>
              產品管理 </router-link>                       
            </li>
            <li class="nav-item">            
              <router-link class="nav-link"
              to="/supplier/supplierOrders"> 
              <i class="far fa-list-alt"></i>
              訂單管理 </router-link>                       
            </li>
        </ul>
				</div>
			</div>
		</nav> -->

</template>
<script>

export default {
    data(){
      return{
        user_name:'',
      }
    },
    created(){
      this.user_name = localStorage.getItem('user');
    }
}
</script>
