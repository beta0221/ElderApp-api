<template>
    
      
     <nav id="sidebarMenu" class="col-md-2 col-lg-1 d-md-block bg-light sidebar collapse">
       
      <div class="sidebar-sticky pt-3">

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
          <span>管理員</span>
          <a class="d-flex align-items-center text-muted" href="#" aria-label="Add a new report">
            <span data-feather="plus-circle"></span>
          </a>
        </h6>
    
         <ul class="nav flex-column mb-2">
          <li class="nav-item mt-3">            
              <router-link class="nav-link"
              to="/supplier/admin/supplierProducts">        
              商品管理 </router-link>                       
          </li>
          <li class="nav-item">            
              <router-link class="nav-link"
              to="/supplier/admin/supplierOrders"> 
              訂單管理 </router-link>                       
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
    
}
</script>