<template>
  <div>

    <member-detail></member-detail>
    <member-tree></member-tree>

    <div>
      <v-btn color="info" @click="executeExpired">執行效期檢查</v-btn>

      <div style="display:inline-block;width:240px;float:right;">
        <v-text-field
          v-model.lazy="searchText"
          @keyup.native.enter="search"
          append-icon="search"
          label="搜尋"
          single-line
          hide-details
        ></v-text-field>
      </div>

      <div style="display:inline-block;width:240px;float:right;margin:0 20px;">
        <v-select v-model="searchColumn" :items="headers" label="欄位"></v-select>
      </div>

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
          <td
            class="text-xs-left org_rank"
            @click="(props.item.org_rank>=1)?getMemberGroupMembers(props.item.id,props.item.name):''"
          >
            <v-icon>{{ org_rank[props.item.org_rank]}}</v-icon>
          </td>
          <td class="text-xs-left tree-icon" @click="showGroupTree(props.item.id,props.item.id_code)">
            <v-icon>supervised_user_circle</v-icon>
          </td>
          <td
            class="text-xs-left name"
            @click="getMemberDetail(props.item.id,props.item.name)"
            :class="gender[props.item.gender]"
          >{{ props.item.name }}</td>
          <td class="text-xs-left">{{ props.item.email }}</td>
          <td class="text-xs-left">{{ props.item.id_number }}</td>
          <td class="text-xs-left">{{ props.item.birthdate }}</td>
          
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
export default {
  components:{
    MemberDetail,
    MemberTree,
  },
  data() {
    return {
      dialog: false,
      dialogUserId: "",
      dialogName: "",
      dialogText: "",

      searchText: "",
      searchColumn:"",
      totalDesserts: 0,
      desserts: [],
      loading: true,
      pagination: {'sortBy':'id','descending':true},

      gender: { 1: "blue--text", 0: "red--text" },
      valid: {
        0: { text: "無效", color: "red--text" },
        1: { text: "有效", color: "green--text" }
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
        "0": "",
        "1": "supervised_user_circle",
        "2": "stars"
      },
      headers: [
        { text: "#", value: "id" },
        { text: "職務", value: "org_rank" },
        { text: "組織", value: "" },
        { text: "姓名", value: "name" },
        { text: "信箱", value: "email" },
        { text: "身分證", value: "id_number" },
        { text: "生日", value: "birthdate" },
        // { text: "位階", value: "rank" },
        { text: "推薦人", value: "inviter" },
        // { text: "推薦人電話", value: "inviter_phone" },
        { text: "入會日期", value: "created_at" },
        { text: "效期到期日", value: "expiry_date" },
        { text: "效期", value: "valid" },
        { text: "會員狀態", value: "pay_status" },
        
      ]
    };
  },
  watch: {
    pagination: {
      handler() {
        this.getDataFromApi().then(data => {
          this.desserts = data.items;
          this.totalDesserts = data.total;
        });
      }
    }
  },
  created() {
    if (!User.loggedIn()) {
      let from_url = window.location.pathname;
      this.$router.push({ name: "login",params:{'from_url':from_url}});
      // this.$router.push({path:'/member'});
    }
  },
  methods: {
    search() {
      if (!this.searchText) {
        this.getDataFromApi().then(data => {
          this.desserts = data.items;
          this.totalDesserts = data.total;
        });
      } else {
        axios
          .get("/api/search-member", {
            params: {
              searchColumn:this.searchColumn,
              searchText: this.searchText
            }
          })
          .then(res => {
            console.log(res);
            this.loading = true;
            setTimeout(() => {
              this.loading = false;
              this.desserts = res.data;
            }, 300);
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    customFilter(items, search, filter) {
      console.log({ items: items, search: search, filter: filter });
      search = search.toString().toLowerCase();
      return items.filter(row => filter(row["type"], search));
    },
    toValid(id, index) {
      if (
        this.desserts[index]["valid"] == 0 &&
        this.desserts[index]["expiry_date"] != null
      ) {
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
    getMemberDetail(id, name) {
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
              // console.log(res.data[i]['created_at']);
            }
            this.dialogText = text;
          } else {
            this.dialogText = "";
          }

          // console.log(res);
        })
        .catch(error => {
          console.log(error);
        });
    },
    executeExpired() {
      axios
        .post("/api/executeExpired", {})
        .then(res => {
          if (res.data.s == 1) {
            this.getDataFromApi().then(data => {
              this.desserts = data.items;
              this.totalDesserts = data.total;
            });
          } else {
            alert("系統錯誤，未正常運作。");
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
            // console.log(res);
            if (res.data.s == 1) {
              this.desserts[index]["pay_status"]++;
            }

            if (this.desserts[index]["pay_status"] == 3) {
              this.desserts[index]["expiry_date"] = res.data.d;
              this.desserts[index]["valid"] = 1;
            }
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    getDataFromApi() {
      this.loading = true;
      return new Promise((resolve, reject) => {
        const { sortBy, descending, page, rowsPerPage } = this.pagination;

        // console.log(this.pagination);
        axios
          .get("/api/get-members", {
            params: {
              page: this.pagination.page,
              rowsPerPage: this.pagination.rowsPerPage,
              descending: this.pagination.descending,
              sortBy: this.pagination.sortBy
            }
          })
          .then(res => {
            // console.log(res.data.users);
            // console.log(res.data.total);
            let items = res.data.users;
            // const total = items.length
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

            setTimeout(() => {
              this.loading = false;
              resolve({
                items,
                total
              });
            }, 300);
          })
          .catch(error => {
            Exception.handle(error);
            User.logout();
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
