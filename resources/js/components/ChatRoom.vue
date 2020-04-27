<template>
  <div class="container">
    <div class="row">
      <div class="col-md-3">
        <div class="card">
          <div class="card-header">Members</div>

          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li
                class="list-group-item"
                v-for="(member, id) in members"
                :key="id"
              >{{ member.user.name }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-9">
        <div class="card">
          <div class="card-header">Chats</div>

          <div class="card-body">
            <dl v-for="message in messages" v-bind:key="message.id">
              <dt
                :class="{  'text-right': message.user.id === authUser.username }"
              >{{ message.user.name }}</dt>
              <dd
                :class="{  'text-right': message.user.id === authUser.username }"
              >{{ message.text }}</dd>
            </dl>

            <hr />

            <span class="help-block" v-if="status" v-text="status" style="font-style: italic;"></span>

            <form @submit.prevent="sendMessage" method="post">
              <div class="input-group">
                <input
                  type="text"
                  v-model="newMessage"
                  class="form-control"
                  placeholder="Type your message..."
                />

                <div class="input-group-append">
                  <button class="btn btn-primary">Send</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { StreamChat } from "stream-chat";

export default {
  name: "ChatRoom",
  props: {
    authUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      token: null,
      channel: null,
      client: null,
      members: [],
      messages: [],
      newMessage: "",
      status: ""
    };
  },
  async created() {
    await this.getToken();
    await this.initializeStream();
    await this.initializeChannel();
  },
  methods: {
    async getToken() {
      const { data } = await axios.post("/api/tokens", {
        username: this.authUser.username
      });
      this.token = data.token;
    },
    async initializeStream() {
      this.client = new StreamChat(process.env.MIX_STREAM_API_KEY, {
        timeout: 9000
      });
      await this.client.setUser(
        { id: this.authUser.username, name: this.authUser.username },
        this.token
      );
    },
    async initializeChannel() {
      this.channel = this.client.channel("messaging", "Chatroom");

      const { members, messages } = await this.channel.watch();

      this.members = members;
      this.messages = messages;

      this.channel.on("message.new", event => {
        this.messages.push({
          text: event.message.text,
          user: event.message.user
        });
      });

      this.channel.on("member.added", event => {
        this.members.push(event);

        this.status = `${event.user.name} joined the chat`;
      });
    },
    async sendMessage() {
      await this.channel.sendMessage({
        text: this.newMessage
      });

      this.newMessage = "";
    }
  }
};
</script>
