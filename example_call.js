callWordpressAjax = async (dataMap) => {
  const body = new URLSearchParams();
  body.append("action", dataMap.action);
  // append all dataMap items to body
  for (const [key, value] of Object.entries(dataMap)) {
    body.append(key, value);
  }

  return await (
    await fetch("https://altoqueperuwk.com/wp-admin/admin-ajax.php", {
      headers: {
        "content-type": "application/x-www-form-urlencoded",
      },
      referrer: "https://altoqueperuwk.com/wp-admin/",
      body: body,
      method: "POST",
      mode: "cors",
      credentials: "include",
    })
  ).json();
};

callWordpressAjax({
  action: "altoke_get_user_profile",
  some_input: "some_value",
});
