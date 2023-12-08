import app from 'flarum/admin/app';
import ChatGptSettings from "./components/ChatGptSettings";

app.initializers.add('muhammedsaidckr/flarum-chatgpt', () => {
  console.log('Hello, admin!')
  app.extensionData
    .for('muhammedsaidckr-chatgpt')
    .registerPermission(
      {
        label: app.translator.trans('muhammedsaidckr-chatgpt.admin.permissions.use_chatgpt_assistant_label'),
        icon: 'fas fa-comment',
        permission: 'discussion.useChatGPTAssistant',
        allowGuest: false,
      },
      'start'
    ).registerPage(ChatGptSettings);
});
