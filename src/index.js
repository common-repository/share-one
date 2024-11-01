import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('share-one/wall-of-love', {
    title: 'Share.one - Wall of Love',
    icon: 'heart',
    category: 'widgets',
    edit() {
        const randomId = Math.random().toString(36).substring(7);
        const { serverUrl, uuid } = shareOneData;
        setTimeout(() => {
            const wol_js_url = `${serverUrl}/walloflove/${uuid}.js?div_id=${randomId}`;
            const script = document.createElement('script');
            script.src = wol_js_url;
            script.defer = true;
            document.body.appendChild(script);
        }, 1000);

        return (
            <div {...useBlockProps()}>
                {uuid ? (
                    <p id={randomId}></p>
                ) : (
                    <p><em>No UUID defined in Share.one settings.</em></p>
                )}
            </div>
        );
    },
    save() {
        return (
            <div {...useBlockProps.save()}>
                <p>[share_one_wall_of_love]</p>
            </div>
        );
    },
});
