/**
 * MIT License
 *
 * Copyright (c) 2020 Kento Oki
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

window.addEventListener('load', function () {
    $('#button-login').on('click', function () {
        $(this).attr('disabled')

        let username = $('#username').val()
        let password = $('#password').val()

        if (username === '' || password === '') {
            alert('username or password is empty')
            $(this).removeAttr('disabled')
            return
        }

        $.post(`/api/`, `a=login&u=${username}&p=${password}`)
            .done(function (data) {
                //console.log(`done -> ${data}`)

                let data_json = JSON.parse(data)
                let result_status = data_json.status
                let result_message = data_json.error

                if (result_status === 0)
                {
                    alert(result_message)
                    $(this).removeAttr('disabled')
                    return
                }
                else if (result_status === 1)
                {
                    location.reload()
                }
            })
            .fail(function () {

            })
            .always(function () {
                $(this).removeAttr('disabled')
            })
    })

    $('#button-logout').on('click', function () {
        $.post('/api/', 'a=logout')
            .done(function (data) {
                console.log(data)
            })
            .fail(function () {})
            .always(function () {
                location.reload()
            })
    })

    $('#button-gentoken').on('click', function () {
        $(this).attr('disabled')

        $.post('/api/', `a=gentoken`)
            .done(function (data) {
                console.log(data)

                let data_json = JSON.parse(data)
                let result_status = data_json.status
                let result_message = data_json.error

                location.reload()
            })
            .fail(function () {})
            .always(function () {
                $(this).removeAttr('disabled')
            })
    })

    $('#button-send').on('click', function () {
        $(this).attr('disabled')

        let recipient = $('#recipient').val()
        let amount = $('#amount').val()

        if (amount <= 0)
        {
            alert('amount must be greater than zero')
            $(this).removeAttr('disabled')
            return
        }

        if (recipient === '')
        {
            alert('recipient must not be empty')
            $(this).removeAttr('disabled')
            return
        }

        $.post('/api/', `a=send&amount=${amount}&recipient=${recipient}`)
            .done(function (data) {
                console.log(data)

                let data_json = JSON.parse(data)
                let result_status = data_json.status
                let result_message = data_json.error
                
                if (result_status === 0)
                {
                    alert(result_message)
                    $(this).removeAttr('disabled')
                    return
                }
                else if (result_status === 1)
                {
                    location.reload()
                }
            })
            .fail(function () {})
            .always(function () {
                $(this).removeAttr('disabled');
            })
    })

    $('#button-register').on('click', function () {
        $(this).attr('disabled')

        let username = $('#username').val()
        let password = $('#password').val()

        if (username === '' || password === '') {
            alert('username or password is empty')
            $(this).removeAttr('disabled');
            return
        }

        $.post(`/api/`, `a=register&u=${username}&p=${password}`)
            .done(function (data) {
                //console.log(`done -> ${data}`)

                let data_json = JSON.parse(data)
                let result_status = data_json.status
                let result_message = data_json.error

                if (result_status === 0) {
                    alert(result_message)
                    $(this).removeAttr('disabled')
                    return
                }
                else if (result_status === 1)
                {
                    location.reload()
                }
            })
            .fail(function () {})
            .always(function () {
                $(this).removeAttr('disabled')
            })
    })
})