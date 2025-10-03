import app from 'flarum/admin/app';
import ExtensionPage, { ExtensionPageAttrs } from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';

// Fallback models in case cached models are not available
const FALLBACK_MODELS = [
  'gpt-4.5-preview',
  'gpt-4o',
  'gpt-4o-mini',
  'gpt-4-turbo',
  'gpt-4',
  'gpt-3.5-turbo',
  'gpt-3.5-turbo-instruct',
  'o1-preview',
  'o1-mini',
  'chatgpt-4o-latest',
];

export default class ChatGptSettings extends ExtensionPage {
  loading!: boolean;
  isFetchingModels!: boolean;
  models!: Record<string, string>;

  oninit(vnode: any) {
    super.oninit(vnode);
    this.loading = false;
    this.isFetchingModels = false;
    this.models = this.getModels();
  }

  getModels() {
    try {
      const cachedModels = app.data.settings['muhammedsaidckr-chatgpt.cached_models'];
      if (cachedModels && cachedModels !== '[]') {
        const parsed = JSON.parse(cachedModels);
        if (Array.isArray(parsed) && parsed.length > 0) {
          return parsed.reduce((acc, model) => {
            acc[model.id] = model.id;
            return acc;
          }, {});
        }
      }
    } catch (e) {
      console.error('Failed to parse cached models:', e);
    }

    // Return fallback models
    return FALLBACK_MODELS.reduce((acc, modelId) => {
      acc[modelId] = modelId;
      return acc;
    }, {});
  }

  fetchModels() {
    this.isFetchingModels = true;

    app
      .request<{ models: any[]; count: number; last_fetched: number }>({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/chatgpt/fetch-models',
      })
      .then(
        (response) => {
          this.isFetchingModels = false;

          // Update cached models in settings
          app.data.settings['muhammedsaidckr-chatgpt.cached_models'] = JSON.stringify(response.models);
          app.data.settings['muhammedsaidckr-chatgpt.models_last_fetched'] = response.last_fetched;

          // Refresh models list
          this.models = this.getModels();

          app.alerts.show(
            {
              type: 'success',
            },
            app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.models_fetched_success', {
              count: response.count,
            })
          );

          m.redraw();
        },
        (error) => {
          this.isFetchingModels = false;

          app.alerts.show(
            {
              type: 'error',
            },
            app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.fetch_models_error')
          );

          m.redraw();
        }
      );
  }

  getLastFetchedText() {
    const timestamp = parseInt(app.data.settings['muhammedsaidckr-chatgpt.models_last_fetched'] || '0');

    if (timestamp === 0) {
      return app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.models_never_fetched');
    }

    const date = new Date(timestamp * 1000);
    return app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.models_last_fetched', {
      date: date.toLocaleString(),
    });
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
                a: <a href="https://platform.openai.com/account/api-keys" target="_blank" rel="noopener" />,
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
              options: this.models,
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.model_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.model_help', {
                a: <a href="https://platform.openai.com/docs/models/overview" target="_blank" rel="noopener" />,
              }),
            })}
            <div className="Form-group">
              <label>{app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.fetch_models_label')}</label>
              <div>
                <Button
                  className="Button Button--primary"
                  onclick={() => this.fetchModels()}
                  loading={this.isFetchingModels}
                  disabled={this.isFetchingModels}
                >
                  {app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.fetch_models_button')}
                </Button>
                <p className="helpText">{this.getLastFetchedText()}</p>
              </div>
            </div>
            {this.buildSettingComponent({
              setting: 'muhammedsaidckr-chatgpt.max_tokens',
              type: 'number',
              label: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.max_tokens_label'),
              help: app.translator.trans('muhammedsaidckr-chatgpt.admin.settings.max_tokens_help', {
                a: <a href="https://help.openai.com/en/articles/4936856" target="_blank" rel="noopener" />,
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
