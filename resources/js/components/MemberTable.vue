<template>
  <div>

    <FlashAlert/>
    <member-detail></member-detail>
    <member-tree></member-tree>
    <send-money-panel></send-money-panel>
    <InviterPanel></InviterPanel>
    <MemberCart ref="memberCart" />
    <TextareaModal />

    <div>
      
      <div style="display:inline-block;width:160px;margin-left:20px;">
        <v-select v-model="searchColumn" :items="columns" item-value="value" label="搜尋欄位"></v-select>
      </div>
      <div v-show="show_level" style="display:inline-block;width:160px;margin-left:20px;">
        <v-select v-model="searchValue" :items="level" item-value="value" label="職位" @change="searchByColumn"></v-select>
      </div>
      <div v-show="show_payStatus" style="display:inline-block;width:160px;margin-left:20px;">
        <v-select v-model="searchValue" :items="payStatus" item-value="value" label="狀態" @change="searchByColumn"></v-select>
      </div>
      <div v-show="show_role" style="display:inline-block;width:160px;margin-left:20px;">
        <v-select v-model="searchValue" :items="role" item-value="value" label="身份" @change="searchByColumn"></v-select>
      </div>
      <div v-show="show_searchText" style="display:inline-block;width:160px;margin-left:20px;">
        <v-text-field
          v-model.lazy="searchValue"
          @keyup.native.enter="search"
          append-icon="search"
          label="搜尋"
          single-line
          hide-details
        ></v-text-field>
      </div>
      <v-btn color="success" style="float:right">
        <a href="/member/join" target="_blank" style="color:#fff;text-decoration:none">新增會員</a>
      </v-btn>
    </div>

    <div>
      <v-btn color="info" @click="selectAll">全選</v-btn>
      <v-btn @click="nextStatusRequest">下階段</v-btn>
      <v-btn @click="addAllUsersToCart">加入清單</v-btn>
      <v-btn @click="renewGroupUsers" color="success">批次續會</v-btn>
      <v-btn @click="showMemberCart" color="warning">選取清單</v-btn>
    </div>


    <div>
      <v-dialog v-model="dialog" max-width="480px">
        <v-card>
          <v-card-title class="headline">姓名：{{dialogName}}</v-card-title>

          <v-card-text>
            <p v-html="dialogText"></p>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="green darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      

    </div>

    <div>
      <v-data-table
        :headers="headers"
        :items="desserts"
        :rows-per-page-items="[15,30]"
        :pagination.sync="pagination"
        :total-items="totalDesserts"
        :loading="loading"
        class="elevation-1"
      >
        <template v-slot:items="props">
          <td class="text-xs-left">{{ props.index + 1 }}</td>
          <td>
            <input type="checkbox" v-model="props.item.isCheck">
          </td>
          <td>
            <v-btn fab small @click="addToCart(props.item)">+</v-btn>
          </td>
          <td class="text-xs-left org_rank">
            {{ (org_rank[props.item.org_rank])?org_rank[props.item.org_rank]:'無'}}
          </td>
          <td class="text-xs-left tree-icon" @click="showGroupTree(props.item.id,props.item.id_code)">
            <v-icon>supervised_user_circle</v-icon>
          </td>
          <td
            class="text-xs-left name"
            @click="getMemberDetail(props.index,props.item.id,props.item.name)"
            :class="gender[props.item.gender]"
          >{{ props.item.name }}</td>
          
          <td class="text-xs-left">{{ props.item.inviter }}</td>
          
          <td class="text-xs-left">{{ (props.item.created_at).substring(0,10) }}</td>
          <td
            class="text-xs-left history"
            @click="getPayHistory(props.item.id,props.item.name)"
          >{{ props.item.expiry_date }}</td>
          <td
            class="text-xs-left valid"
            @click="toValid(props.item.id,props.index)"
            :class="valid[props.item.valid].color"
          >{{ valid[props.item.valid].text }}</td>
          <td class="text-xs-left">
            <v-btn
              @click="clickPayStatus(props.item.id,props.index)"
              :color="status[props.item.pay_status].color"
            >{{status[props.item.pay_status].text}}</v-btn>
          </td>
          
        </template>
      </v-data-table>
    </div>
  </div>
</template>


<script>
import MemberDetail from "./MemberDetail";
import MemberTree from "./MemberTree";
import SendMoneyPanel from "./SendMoneyPanel";
import InviterPanel from "./Member/InviterPanel"
import MemberCart from "./Member/memberCart.vue"
import FlashAlert from "./FlashAlert.vue"
import TextareaModal from "./TextareaModal.vue"

export default {
  components:{
    MemberDetail,
    MemberTree,
    SendMoneyPanel,
    InviterPanel,
    MemberCart,
    FlashAlert,
    TextareaModal
  },
  data() {
    return {
      dialog: false,
      dialogUserId: "",
      dialogName: "",
      dialogText: "",
      editingIndex:null,
      isSelectAll:false,

      blurSearch:false,
      searchColumn:null,
      searchValue:null,
      totalDesserts: 0,
      desserts: [],
      loading: false,
      pagination: {'page':1,'rowsPerPage':15,'sortBy':'id','descending':true},

      gender: { 1: "blue--text", 0: "red--text" },
      valid: {
        0: { text: "無效", color: "red--text" },
        1: { text: "有效", color: "green--text" },
      },
      status: {
        "0": { text: "免費", color: "grey" },
        "1": { text: "申請中", color: "warning" },
        "2": { text: "已繳清", color: "info" },
        "3": { text: "完成", color: "success" }
      },
      rank: {
        "1": "",
        "2": "組長",
        "3": "理事"
      },
      org_rank: {
        "2": "小天使",
        "3": "大天使",
        "4": "守護天使",
        "5": "領航天使",
      },
      headers: [
        { text: "#", value: "id" },
        { text: "勾選"},
        { text: "加入清單"},
        { text: "職務", value: "org_rank" },
        { text: "組織", value: "" },
        { text: "姓名", value: "name" },
        { text: "推薦人", value: "inviter" },
        { text: "入會日期", value: "created_at" },
        { text: "效期到期日", value: "expiry_date" },
        { text: "效期", value: "valid" },
        { text: "會員狀態", value: "pay_status" },
      ],
      columns:[
        {text:'欄位',value:null},
        {text:'id(以,分隔)',value:'id'},
        {text:'職務',value:'org_rank'},
        {text:'姓名(以,分隔)',value:'name'},
        {text:'身分證',value:'id_number'},
        {text:'手機號碼',value:'phone'},
        {text:'會員狀態',value:'pay_status'},
        {text:'推薦人',value:'inviter'},
        {text:'身份',value:'role'},
      ],
      show_level:false,
      show_payStatus:false,
      show_searchText:false,
      show_role:false,
      show_id:false,
      level:[
        {text:'職位',value:null},
        {text:'小天使',value:2},
        {text:'大天使',value:3},
        {text:'守護天使',value:4},
        {text:'領航天使',value:5},
      ],
      payStatus:[
        {text:'狀態',value:null},
        {text:'免費',value:0},
        {text:'申請中',value:1},
        {text:'已繳清',value:2},
        {text:'完成',value:3},
      ],
      role:[
        {text:'身份',value:null},
        {text:'老師',value:4}
      ]
    };
  },
  watch: {
    pagination(val){
      this.search();
    },
    searchColumn(val){
      this.blurSearch = false;
      this.show_level = false;
      this.show_payStatus = false;
      this.show_searchText = false;
      this.show_role = false;
      this.searchValue = null;
      switch (val) {
        case 'org_rank':
          this.show_level = true;
          break;
        case 'pay_status':
          this.show_payStatus = true;
          break;
        case 'name':
        case 'id_number':
        case 'phone':
        case 'inviter':
          this.show_searchText = true;
          this.blurSearch = true;
          break;
        case 'id':
          this.show_searchText = true;
          this.blurSearch = false;
          break;
        case 'role':
          this.show_role = true;
          break;
        default:
          this.pagination.page = 1;
          this.search();
      }
    }
  },
  destroyed(){
    EventBus.$off('updateMemberSuccess');
  },
  mounted(){
    EventBus.$on("updateMemberSuccess", user => {
      this.search();
    });
  },
  created() {
    this.search();
    User.authOnly();
  },
  methods: {
    searchByColumn(){
      this.pagination.page = 1;
      this.search();
    },
    search() {
    if(this.loading == true){
      return;
    }
    this.isSelectAll = false;
    this.loading = true;
      axios
          .get("/api/get-members", {
            params: {
              page: this.pagination.page,
              rowsPerPage: this.pagination.rowsPerPage,
              descending: this.pagination.descending,
              sortBy: this.pagination.sortBy,
              column:this.searchColumn,
              value:this.searchValue,
              blurSearch:this.blurSearch
            }
          })
          .then(res => {
            this.desserts = res.data.users;
            this.totalDesserts = res.data.total;
            setTimeout(() => {  
              this.loading = false;    
            }, 300);
            if(res.data.queryResult != null){
              let content = `＊查無資料：\n${res.data.queryResult.nameNotFound.join()}\n＊重複姓名：\n${res.data.queryResult.nameRepeat.join()}
              `;
              EventBus.$emit('showTextAreaModal',content);

            }
          })
          .catch(error => {
            Exception.handle(error);
          })
    },
    toValid(id, index) {
      if(!confirm('確定增加會員效期？')){
        return;
      }
      axios
      .post("/api/toValid", {
        id: id
      })
      .then(res => {
        this.search();
      })
      .catch(error => {
        Exception.handle(error);
      });
    },
    addGroupMember() {
      axios
        .post("/api/addGroupMember", {
          leaderId: this.dialogUserId,
          addAccount: this.addAcount
        })
        .then(res => {
          if (res.data.s == 1) {
            this.group_members.push(res.data.addUser);
          } else {
            alert(res.data.m);
          }
          console.log(res);
        })
        .catch(err => {
          console.error(err);
        });
    },
    showGroupTree(id,id_code){
      let user = {'id_code':id_code,'id':id};
        EventBus.$emit("showMemberTree",user);
    },
    getMemberDetail(index,id, name) {
      this.editingIndex = index;
      let user = {'id':id,'name':name};
      EventBus.$emit('showMemberDetail',user);
    },
    getPayHistory(id, name) {
      this.dialog = true;
      axios
        .get(`/api/getPayHistory/${id}`)
        .then(res => {
          this.dialogName = name;

          if (res.data.length != 0) {
            let text = "";
            for (let i in res.data) {
              text += res.data[i]["created_at"].substring(0, 10) + "<br>";
            }
            this.dialogText = text;
          } else {
            this.dialogText = "";
          }
        })
        .catch(error => {
          console.log(error);
        });
    },
    clickPayStatus(id, index) {
      if (this.desserts[index]["pay_status"] < 3) {
        axios
          .post("/api/changePayStatus", {
            id: id
          })
          .then(res => {
            this.search();
          })
          .catch(error => {
            Exception.handle(error);
          });
      }
    },
    renewGroupUsers(){
      if(!confirm('確定增加會員效期？')){ return; }
      axios
      .post("/api/toValid", {
        id: this.getSelectedArray(),
      })
      .then(res => {
        this.search();
      })
      .catch(error => {
        Exception.handle(error);
      });
    },
    selectAll(){
      this.isSelectAll = !this.isSelectAll;
      this.desserts.forEach((item,i)=>{
        this.$set(this.desserts[i],'isCheck',this.isSelectAll);
      });
    },
    getSelectedArray(){
      let selectedArray = [];
      this.desserts.forEach((item)=>{
        if(item.isCheck != true){ return; }
          if(item.id != undefined){
            selectedArray.push(item.id);
          }
        });
      return selectedArray;
    },
    nextStatusRequest(){
      axios
      .post("/api/changePayStatus", {
        id: this.getSelectedArray(),
      })
      .catch(error => { Exception.handle(error); })
      .then(res => {
        this.search();
      });
      
    },
    showMemberCart(){
      this.$refs.memberCart.showModal();
    },
    addToCart(user){
      this.$refs.memberCart.add(user);
    },
    addAllUsersToCart(){
      this.desserts.forEach((item)=>{
        if(item.isCheck != true){ return; }
          if(item.id != undefined){
            this.$refs.memberCart.add(item);
          }
      });
    }
  }
};
</script>

<style>
.tree-icon,
.history,
.valid,
.name,
.org_rank {
  cursor: pointer;
}
.tree-icon:hover,
.history:hover,
.valid:hover,
.name:hover,
.org_rank:hover {
  background-color: lightgrey;
  color: #fff;
}
</style>
