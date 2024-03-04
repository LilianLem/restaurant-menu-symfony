import "@api-platform/admin";
import React from "react";
import { AppBar, Layout, ToggleThemeButton } from "react-admin";
import { HydraAdmin } from "@api-platform/admin";

/*interface Props {
    entrypoint: string
}*/

export const FixedAppBar = () => <AppBar toolbar={<ToggleThemeButton />} />;
const FixedLayout = (props) => <Layout {... props} appBar={FixedAppBar} />;

// Replace with your own API entrypoint
// For instance if https://example.com/api/books is the path to the collection of book resources, then the entrypoint is https://example.com/api
export default (props/*: Props*/) => (
    <HydraAdmin
        entrypoint={props.entrypoint}
        darkTheme={{ palette: { mode: "dark" }}}
        layout={FixedLayout}
    />
);