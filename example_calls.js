callWordpressAjax = async (dataMap) => {
  const formData = new FormData();

  // Append all dataMap items to formData
  for (const [key, value] of Object.entries(dataMap)) {
    formData.append(key, value);
  }
  return await (
    await fetch("https://altoqueperuwk.com/wp-admin/admin-ajax.php", {
      method: "POST",
      body: formData,
      credentials: "include",
    })
  ).json();
};

// Example usage for image upload
const uploadImage = async (file) => {
  try {
    const response = await callWordpressAjax({
      action: "altoke_upload_image",
      image: file,
    });

    if (response.success) {
      console.log("Image uploaded successfully:", response.data);
    } else {
      console.error("Image upload failed:", response.data.message);
    }
  } catch (error) {
    console.error("Error uploading image:", error);
  }
};

callWordpressAjax({
  action: "altoke_get_user_profile",
  some_input: "some_value",
});

// add image upload call function
