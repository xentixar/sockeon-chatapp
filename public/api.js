const api = async (uri, method, data, isProtected = false) => {
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    };

    if (isProtected) {
        headers['Authorization'] = localStorage.getItem('access_token');
    }

    const options = {
        method: method,
        headers: headers
    }

    if (method !== 'GET') {
        options['body'] = JSON.stringify(data)
    }

    const response = await fetch(uri, options);

    const json = await response.json();
    if (response.ok) {
        return json;
    } else {
        alert(json.message);
    }
}

export {api}