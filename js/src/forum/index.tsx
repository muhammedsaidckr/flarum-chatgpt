import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import PostUser from 'flarum/forum/components/PostUser';

app.initializers.add('muhammedsaidckr-chatgpt', () => {
  extend(PostUser.prototype, 'view', function (view: any) {
    const user = this.attrs?.post?.user();

    if (!user || app.forum.attribute('chatGptUserPromptId') !== user.id()) return;

    if (view.children && Array.isArray(view.children)) {
      view.children.push(
        <div className="UserPromo-badge">
          <div className="badge">{app.forum.attribute('chatGptBadgeText')}</div>
        </div>
      );
    }
  });
});
