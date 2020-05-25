<template>
  <div>

    <member-detail></member-detail>
    <member-tree></member-tree>
    <send-money-panel></send-money-panel>

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
export default {
  components:{
    MemberDetail,
    MemberTree,
    SendMoneyPanel,
  },
  data() {
    return {
      dialog: false,
      dialogUserId: "",
      dialogName: "",
      dialogText: "",
      editingIndex:null,

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
        {text:'職務',value:'org_rank'},
        {text:'姓名',value:'name'},
        {text:'身分證',value:'id_number'},
        {text:'手機號碼',value:'phone'},
        {text:'會員狀態',value:'pay_status'},
        {text:'身份',value:'role'},
      ],
      show_level:false,
      show_payStatus:false,
      show_searchText:false,
      show_role:false,
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
          this.show_searchText = true;
          this.blurSearch = true;
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
  created() {
    this.search();
    User.authOnly();
    EventBus.$on("updateMemberSuccess", user => {
      // this.$set(this.desserts,this.editingIndex,user);
      this.search();
    });
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
    this.loading = true;
      this.getDataFromApi().then(data => {
          this.desserts = data.items;
          this.totalDesserts = data.total;
          setTimeout(() => {  
            this.loading = false;    
          }, 300);
      });
    },
    toValid(id, index) {
      if (this.desserts[index]["valid"] == 0 && this.desserts[index]["expiry_date"] != null) {
        axios
          .post("/api/toValid", {
            id: id
          })
          .then(res => {
            if (res.data.s == 1) {
              this.desserts[index]["valid"] = 1;
              this.desserts[index]["expiry_date"] = res.data.d;
            }
            console.log(res);
          })
          .catch(error => {
            console.log(error);
          });
      }
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
            if (res.data.s == 1) {
              this.desserts[index]["pay_status"]++;
              if (this.desserts[index]["pay_status"] == 3) {
                this.desserts[index]["expiry_date"] = res.data.d;
                this.desserts[index]["valid"] = 1;
              }
            }
            if(res.data.s == 0){
              alert(res.data.m);
            }
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    getDataFromApi() {
      return new Promise((resolve, reject) => {
        const { sortBy, descending, page, rowsPerPage } = this.pagination;
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
            let items = res.data.users;
            const total = res.data.total;

            if (this.pagination.sortBy) {
              items = items.sort((a, b) => {
                const sortA = a[sortBy];
                const sortB = b[sortBy];

                if (descending) {
                  if (sortA < sortB) return 1;
                  if (sortA > sortB) return -1;
                  return 0;
                } else {
                  if (sortA < sortB) return -1;
                  if (sortA > sortB) return 1;
                  return 0;
                }
              });
            }
            resolve({
              items,
              total
            });
          })
          .catch(error => {
            Exception.handle(error);
          })
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
