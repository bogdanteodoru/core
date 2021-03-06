import ComposerBody from 'flarum/components/ComposerBody';
import icon from 'flarum/helpers/icon';

/**
 * The `EditPostComposer` component displays the composer content for editing a
 * post. It sets the initial content to the content of the post that is being
 * edited, and adds a header control to indicate which post is being edited.
 *
 * ### Props
 *
 * - All of the props for ComposerBody
 * - `post`
 */
export default class EditPostComposer extends ComposerBody {
  init() {
    super.init();

    this.editor.props.preview = () => {
      // If the composer backdrop is visible, assume we're on mobile and need to
      // minimize the composer in order to see the preview. We do this as a
      // timeout so that it occurs after the click handler on the composer
      // itself that shows the composer if minimized.
      if (app.composer.isMobile()) {
        setTimeout(() => app.composer.minimize(), 0);
      }

      m.route(app.route.post(this.props.post));
    };
  }

  static initProps(props) {
    super.initProps(props);

    props.submitLabel = props.submitLabel || app.translator.trans('core.forum.composer_edit.submit_button');
    props.confirmExit = props.confirmExit || app.translator.trans('core.forum.composer_edit.discard_confirmation');
    props.originalContent = props.originalContent || props.post.content();
    props.user = props.user || props.post.user();

    props.post.editedContent = props.originalContent;
  }

  headerItems() {
    const items = super.headerItems();
    const post = this.props.post;

    items.add('title', (
      <h3>
        {icon('pencil')} {' '}
        <a href={app.route.discussion(post.discussion(), post.number())} config={m.route}>
          {app.translator.trans('core.forum.composer_edit.post_link', {number: post.number(), discussion: post.discussion().title()})}
        </a>
      </h3>
    ));

    return items;
  }

  /**
   * Get the data to submit to the server when the post is saved.
   *
   * @return {Object}
   */
  data() {
    return {
      content: this.content()
    };
  }

  onsubmit() {
    this.loading = true;

    const data = this.data();

    this.props.post.save(data).then(
      () => app.composer.hide(),
      this.loaded.bind(this)
    );
  }
}
