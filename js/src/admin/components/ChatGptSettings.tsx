import app from "flarum/admin/app";
import ExtensionPage, {ExtensionPageAttrs} from 'flarum/admin/components/ExtensionPage';

const modelsData = require('../../../models.json');
const models = modelsData.data.reduce((acc, model) => {
  acc[model.id] = model.id;
  return acc;
}, {});
export default class ChatGptSettings extends ExtensionPage {
  oninit(vnode) {
    super.oninit(vnode);
    this.loading = false;
  }

  content() {
    return (
      <div className="ExtensionPage-settings">
        <div className="container">
          <div className="Form">
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.api_key',
              type: 'text',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.api_key_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.api_key_help', {
                a: <a href="https://platform.openai.com/account/api-keys" target="_blank" rel="noopener"/>,
              }),
              placeholder: 'sk-...',
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.base_uri',
              type: 'text',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.base_uri_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.base_uri_help'),
              placeholder: 'api.openai.com',
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.model',
              type: 'dropdown',
              options: models,
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.model_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.model_help', {
                a: <a href="https://platform.openai.com/docs/models/overview" target="_blank" rel="noopener"/>,
              }),
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.max_tokens',
              type: 'number',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.max_tokens_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.max_tokens_help', {
                a: <a href="https://help.openai.com/en/articles/4936856" target="_blank" rel="noopener"/>,
              }),
              default: 100,
            })}
            {/* new setting for moderation */}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.moderation',
              type: 'boolean',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.moderation_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.moderation_help'),
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.user_prompt',
              type: 'text',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.user_prompt_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.user_prompt_help'),
            })}
            {/* new settings for role */}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.role',
              type: 'text',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.role_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.role_help'),
            })}
            {/* new settings for prompt */}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.prompt',
              type: 'text',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.prompt_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.prompt_help'),
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.user_prompt_badge_text',
              type: 'text',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.user_prompt_badge_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.user_prompt_badge_help'),
            })}
            {/*new setting for queue_active */}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.queue_active',
              type: 'boolean',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.queue_active_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.queue_active_help'),
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.answer_duration',
              type: 'number',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.answer_duration_label'),
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.enable_on_reply',
              type: 'boolean',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.enable_on_reply_label'),
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.enable_on_discussion_started',
              type: 'boolean',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.enable_on_discussion_started_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.enable_on_discussion_started_help'),
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.continue_to_reply',
              type: 'boolean',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.continue_to_reply_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.continue_to_reply_help'),
            })}
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.continue_to_reply_count',
              type: 'number',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.continue_to_reply_count_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.continue_to_reply_count_help'),
            })}
            {this.buildSettingComponent({
              type: 'flarum-tags.select-tags',
              setting: 'muhammedsaidckr-chatgpt.enabled-tags',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.enabled_tags_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.enabled_tags_help'),
              options: {
                requireParentTag: false,
                limits: {
                  max: {
                    secondary: 0,
                  },
                },
              },
            })}
            <div className="Form-group">{this.submitButton()}</div>
          </div>
        </div>
      </div>
    );
  }
}
