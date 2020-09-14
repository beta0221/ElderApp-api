<template>
  <div class="container-fluid mt-3">
    <div class="row">
      <div class="col-md-2 col-lg-2">
        <side-bar></side-bar>
      </div>
      <div class="col-md-10 col-lg-10">
        <div class="container-fluid">
          <div class="card text-center row">
            <div class="card-header">
              <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item mb-2 mr-4">
                  <button class="btn btn-primary" @click="selectAll">全選</button>
                </li>
                <li class="nav-item mb-2 mr-4">
                  <button class="btn btn-primary" @click="groupNextStatus">下階段</button>
                </li>
                <li class="nav-item mb-2 mr-4">
                  <button class="btn btn-primary" @click="groupExportExcel">匯出</button>
                </li>
                <li class="nav-item mb-2 mr-3">
                  <select class="form-control" name id v-model="searchColumn">
                    <option
                      v-for="(col,index) in columns"
                      :key="index"
                      :value="col.value"
                    >{{col.text}}</option>
                  </select>
                </li>
                <li class="nav-item mb-2 mr-3">
                  <select
                    class="form-control"
                    name
                    id
                    @change="searchByColumn"
                    v-model="searchValue"
                    v-show="searchColumn=='ship_status'"
                  >
                    <option
                      v-for="opt in statusList"
                      :key="opt.value"
                      :value="opt.value"
                    >{{opt.text}}</option>
                  </select>
                </li>
                <li class="nav-item mb-2" v-show="searchColumn=='created_at'">
                  <input class="form-control" type="date" v-model="searchValue" />
                </li>
                <li class="nav-item mb-2" v-show="searchColumn=='order_numero'">
                  <input class="form-control" type="text" v-model="searchValue" />
                </li>

                <span class="align-right ml-auto">
                  <pagination
                    v-on:getProducts="getOrders"
                    :totalPage="totalPage"
                    :pagination="pagination"
                  ></pagination>
                </span>
              </ul>
            </div>
            <table class="table table-hover">
              <thead>
                <tr>
                  <th v-for="(header,index) in headers" :key="index">{{header.text}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item,index) in orderList" :key="index">
                  <td class="align-middle">{{index+1}}</td>
                  <td class="align-middle">
                    <input
                      type="checkbox"
                      class="form-control"
                      style="height:15px;"
                      v-model="item.isCheck"
                    />
                  </td>
                  <td class="align-middle">
                    <button
                      :class="btnClass[item.ship_status]"
                      @click="nextStatus(item.order_numero)"
                    >{{statusDict[item.ship_status]}}</button>
                  </td>
                  <td
                    class="align-middle"
                    v-for="(detail,index) in item.list"
                    :key="index"
                  >{{detail.name}}</td>
                  <td
                    class="align-middle"
                    @click="getOrderDetail(item.order_numero)"
                  >{{item.order_numero}}</td>
                  <td class="align-middle">{{item.created_at}}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <div
        class="modal fade"
        id="detailModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="detailModalLabel"
        aria-hidden="true"
      >
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <ul class="list-unstyled">
                <li>
                  <h5 class="modal-title" id="detailModal">訂單編號 : {{orderNumero}}</h5>
                </li>
                <li>
                  <h6 class="modal-title">收件人 : {{orderDelievery.receiver_name}}</h6>
                </li>
                <li>
                  <h6 class="modal-title">聯絡電話 : {{orderDelievery.receiver_phone}}</h6>
                </li>
                <li>
                  <h6 class="modal-title">地址 : {{orderDelievery.address}}</h6>
                </li>
              </ul>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <table class="table table-borderless">
                <thead>
                  <tr>
                    <th></th>
                    <th>商品</th>
                    <th>總數</th>
                    <th>總金額</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item,index) in orderDetail" :key="index">
                    <td class="align-middle" v-for="(img,index) in productImageDict" :key="index">
                      <img style="height:100px" :src="img" />
                    </td>
                    <td class="align-middle">{{item.name}}</td>
                    <td class="align-middle">{{item.cash_quantity}}</td>
                    <td class="align-middle">{{item.total_cash}}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import pagination from "./Pagination";
export default {
  components: {
    pagination,
  },
  data() {
    return {
      pagination: {
        sortBy: "id",
        descending: true,
        page: 1,
        rowsPerPage: 15,
      },
      orderList: [],
      totalOrders: 0,
      totalPage: 0,
      searchColumn: null,
      searchValue: null,
      isSelectAll: false,
      orderDelievery: {},
      orderDetail: [],
      orderNumero: "",
      productImageDict: "",
      btnClass: {
        "0": "btn btn-secondary",
        "1": "btn btn-info",
        "2": "btn btn-primary",
        "3": "btn btn-success",
        "4": "btn btn-danger",
      },
      statusDict: {
        "0": "待出貨",
        "1": "準備中",
        "2": "已出貨",
        "3": "已到貨",
        "4": "結案",
      },
      statusList: [
        { text: "待出貨", value: 0 },
        { text: "準備中", value: 1 },
        { text: "已出貨", value: 2 },
        { text: "已到貨", value: 3 },
        { text: "結案", value: 4 },
      ],
      columns: [
        { text: "欄位", value: null },
        { text: "狀態", value: "ship_status" },
        { text: "日期", value: "created_at" },
        { text: "訂單編號", value: "order_numero" },
      ],
      headers: [
        { text: "#" },
        { text: "選取" },
        { text: "狀態", value: "ship_status" },
        { text: "商品" },
        { text: "訂單編號", value: "order_numero" },
        { text: "日期", value: "created_at" },
      ],
    };
  },
  watch: {
    searchColumn(val) {
      this.searchValue = null;
      this.searchColumn = val;
      this.pagination.page = 1;
      this.getOrders();
      console.log("here");
    },
  },
  methods: {
    searchByColumn() {
      this.pagination.page = 1;
      this.getOrders();
    },
    getOrders() {
      axios
        .get("/api/order/getOrders", {
          params: {
            page: this.pagination.page,
            rowsPerPage: this.pagination.rowsPerPage,
            descending: this.pagination.descending,
            sortBy: this.pagination.sortBy,
            column: this.searchColumn,
            value: this.searchValue,
          },
        })
        .then((res) => {
          this.totalOrders = res.data.total;
          this.orderList = res.data.orderList;
          this.totalPage = Math.ceil(
            this.totalOrders / this.pagination.rowsPerPage
          );
          console.log(res);
        })
        .catch((error) => {
          Exception.handle(error);
        });
    },
    getCheckedOrderNumero() {
      let numeroArray = [];
      this.orderList.forEach((order) => {
        if (order.isCheck) {
          numeroArray.push(order.order_numero);
        }
      });
      return numeroArray;
    },
    groupNextStatus() {
      let order_numero_array = this.getCheckedOrderNumero();
      if (order_numero_array.length == 0) {
        alert("請勾選");
        return;
      }
      axios
        .post("/api/order/groupNextStatus", {
          order_numero_array: JSON.stringify(order_numero_array),
        })
        .then((res) => {
          console.error(res);
          this.getOrders();
        })
        .catch((err) => {
          console.error(err);
        });
    },
    selectAll() {
      this.isSelectAll = !this.isSelectAll;
      this.orderList.forEach((order, index) => {
        this.$set(this.orderList[index], "isCheck", this.isSelectAll);
      });
    },
    groupExportExcel() {
      let order_numero_array = this.getCheckedOrderNumero();
      if (order_numero_array.length == 0) {
        alert("請勾選");
        return;
      }
      window.open(
        "/order/downloadOrderExcel?token=" +
          localStorage.getItem("token") +
          "&order_numero_array=" +
          order_numero_array.join(",")
      );
    },
    nextStatus(order_numero) {
      axios
        .post("/api/order/nextStatus", {
          order_numero: order_numero,
        })
        .then((res) => {
          if (res.data.s == 1) {
            this.getOrders();
          } else {
            alert(res.data.m);
          }
        })
        .catch((err) => {
          console.error(err);
        });
    },
    getOrderDetail(numero) {
      this.orderNumero = numero;
      this.openModal();
      axios
        .get(`/api/order/getOrderDetail/${numero}`)
        .then((res) => {
          console.log(res);
          this.orderDelievery = res.data.orderDelievery;
          this.orderDetail = res.data.orders;
          this.productImageDict = res.data.productImageDict;
        })
        .catch((err) => {
          console.error(err);
        });
    },
    openModal() {
      $("#detailModal").modal("show");
    },
  },
  created() {
    this.getOrders();
  },
};
</script>
<style scope>
</style>