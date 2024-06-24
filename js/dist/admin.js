(()=>{var t={739:t=>{"use strict";t.exports=JSON.parse('{"object":"list","data":[{"id":"whisper-1","object":"model","created":1677532384,"owned_by":"openai-internal"},{"id":"gpt-4o-2024-05-13","object":"model","created":1715368132,"owned_by":"system"},{"id":"babbage-002","object":"model","created":1692634615,"owned_by":"system"},{"id":"dall-e-2","object":"model","created":1698798177,"owned_by":"system"},{"id":"gpt-3.5-turbo-16k","object":"model","created":1683758102,"owned_by":"openai-internal"},{"id":"tts-1-hd-1106","object":"model","created":1699053533,"owned_by":"system"},{"id":"tts-1-hd","object":"model","created":1699046015,"owned_by":"system"},{"id":"gpt-3.5-turbo-instruct-0914","object":"model","created":1694122472,"owned_by":"system"},{"id":"gpt-3.5-turbo-instruct","object":"model","created":1692901427,"owned_by":"system"},{"id":"text-embedding-3-small","object":"model","created":1705948997,"owned_by":"system"},{"id":"gpt-4-turbo-2024-04-09","object":"model","created":1712601677,"owned_by":"system"},{"id":"tts-1","object":"model","created":1681940951,"owned_by":"openai-internal"},{"id":"gpt-4-turbo","object":"model","created":1712361441,"owned_by":"system"},{"id":"text-embedding-3-large","object":"model","created":1705953180,"owned_by":"system"},{"id":"gpt-4-1106-preview","object":"model","created":1698957206,"owned_by":"system"},{"id":"gpt-3.5-turbo-1106","object":"model","created":1698959748,"owned_by":"system"},{"id":"gpt-4-0125-preview","object":"model","created":1706037612,"owned_by":"system"},{"id":"gpt-3.5-turbo-0125","object":"model","created":1706048358,"owned_by":"system"},{"id":"gpt-3.5-turbo","object":"model","created":1677610602,"owned_by":"openai"},{"id":"gpt-3.5-turbo-0301","object":"model","created":1677649963,"owned_by":"openai"},{"id":"gpt-4-turbo-preview","object":"model","created":1706037777,"owned_by":"system"},{"id":"tts-1-1106","object":"model","created":1699053241,"owned_by":"system"},{"id":"dall-e-3","object":"model","created":1698785189,"owned_by":"system"},{"id":"gpt-3.5-turbo-16k-0613","object":"model","created":1685474247,"owned_by":"openai"},{"id":"gpt-3.5-turbo-0613","object":"model","created":1686587434,"owned_by":"openai"},{"id":"gpt-4","object":"model","created":1687882411,"owned_by":"openai"},{"id":"text-embedding-ada-002","object":"model","created":1671217299,"owned_by":"openai-internal"},{"id":"gpt-4-1106-vision-preview","object":"model","created":1711473033,"owned_by":"system"},{"id":"davinci-002","object":"model","created":1692634301,"owned_by":"system"},{"id":"gpt-4-0613","object":"model","created":1686588896,"owned_by":"openai"},{"id":"gpt-4-vision-preview","object":"model","created":1698894917,"owned_by":"system"},{"id":"gpt-4o","object":"model","created":1715367049,"owned_by":"system"}]}')}},e={};function a(n){var o=e[n];if(void 0!==o)return o.exports;var s=e[n]={exports:{}};return t[n](s,s.exports,a),s.exports}a.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return a.d(e,{a:e}),e},a.d=(t,e)=>{for(var n in e)a.o(e,n)&&!a.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:e[n]})},a.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),a.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var n={};(()=>{"use strict";a.r(n);const t=flarum.core.compat["common/app"];a.n(t)().initializers.add("muhammedsaidckr/flarum-chatgpt",(function(){}));const e=flarum.core.compat["admin/app"];var o=a.n(e);function s(t,e){return s=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(t,e){return t.__proto__=e,t},s(t,e)}const r=flarum.core.compat["admin/components/ExtensionPage"];var d=a.n(r),i=a(739).data.reduce((function(t,e){return t[e.id]=e.id,t}),{}),l=function(t){var e,a;function n(){return t.apply(this,arguments)||this}a=t,(e=n).prototype=Object.create(a.prototype),e.prototype.constructor=e,s(e,a);var r=n.prototype;return r.oninit=function(e){t.prototype.oninit.call(this,e),this.loading=!1},r.content=function(){return m("div",{className:"ExtensionPage-settings"},m("div",{className:"container"},m("div",{className:"Form"},this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.api_key",type:"text",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.api_key_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.api_key_help",{a:m("a",{href:"https://platform.openai.com/account/api-keys",target:"_blank",rel:"noopener"})}),placeholder:"sk-..."}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.model",type:"dropdown",options:i,label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.model_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.model_help",{a:m("a",{href:"https://platform.openai.com/docs/models/overview",target:"_blank",rel:"noopener"})})}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.max_tokens",type:"number",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.max_tokens_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.max_tokens_help",{a:m("a",{href:"https://help.openai.com/en/articles/4936856",target:"_blank",rel:"noopener"})}),default:100}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.moderation",type:"boolean",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.moderation_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.moderation_help")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.user_prompt",type:"text",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.user_prompt_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.user_prompt_help")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.role",type:"text",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.role_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.role_help")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.prompt",type:"text",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.prompt_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.prompt_help")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.user_prompt_badge_text",type:"text",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.user_prompt_badge_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.user_prompt_badge_help")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.queue_active",type:"boolean",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.queue_active_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.queue_active_help")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.answer_duration",type:"number",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.answer_duration_label")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.enable_on_reply",type:"boolean",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.enable_on_reply_label")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.enable_on_discussion_started",type:"boolean",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.enable_on_discussion_started_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.enable_on_discussion_started_help")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.continue_to_reply",type:"boolean",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.continue_to_reply_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.continue_to_reply_help")}),this.buildSettingComponent({setting:"muhammedsaidckr-chatgpt.continue_to_reply_count",type:"number",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.continue_to_reply_count_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.continue_to_reply_count_help")}),this.buildSettingComponent({type:"flarum-tags.select-tags",setting:"muhammedsaidckr-chatgpt.enabled-tags",label:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.enabled_tags_label"),help:o().translator.trans("muhammedsaidckr-chatgpt.admin.settings.enabled_tags_help"),options:{requireParentTag:!1,limits:{max:{secondary:0}}}}),m("div",{className:"Form-group"},this.submitButton()))))},n}(d());o().initializers.add("muhammedsaidckr-chatgpt",(function(){o().extensionData.for("muhammedsaidckr-chatgpt").registerPermission({label:o().translator.trans("muhammedsaidckr-chatgpt.admin.permissions.use_chatgpt_assistant_label"),icon:"fas fa-comment",permission:"discussion.useChatGPTAssistant",allowGuest:!1},"start").registerPage(l)}))})(),module.exports=n})();
//# sourceMappingURL=admin.js.map