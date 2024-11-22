// ==UserScript==
// @name         携程订单信息提取（新增房间定价）
// @namespace    http://tampermonkey.net/
// @version      2.1
// @description  提取携程订单信息并显示在页面中，同时复制按钮始终可见，新增房间定价信息
// @author       Ayo
// @match        https://ebooking.ctrip.com/*
// @grant        none
// ==/UserScript==

(function () {
    'use strict';

    // 添加主按钮到页面右上角
    const button = document.createElement('button');
    button.innerText = '提取订单信息';
    button.style.position = 'fixed';
    button.style.top = '10px';
    button.style.right = '10px';
    button.style.zIndex = '9999';
    button.style.padding = '10px 20px';
    button.style.backgroundColor = '#007BFF';
    button.style.color = '#fff';
    button.style.border = 'none';
    button.style.borderRadius = '5px';
    button.style.cursor = 'pointer';
    button.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    document.body.appendChild(button);

    // 创建信息显示容器
    const infoDiv = document.createElement('div');
    infoDiv.style.position = 'fixed';
    infoDiv.style.top = '50px';
    infoDiv.style.right = '10px';
    infoDiv.style.zIndex = '9999';
    infoDiv.style.padding = '10px';
    infoDiv.style.backgroundColor = '#fff';
    infoDiv.style.border = '1px solid #ccc';
    infoDiv.style.borderRadius = '5px';
    infoDiv.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    infoDiv.style.maxWidth = '300px';
    infoDiv.style.wordWrap = 'break-word';
    infoDiv.innerText = '请点击“提取订单信息”按钮查看数据';
    document.body.appendChild(infoDiv);

    // 创建复制按钮
    const copyButton = document.createElement('button');
    copyButton.innerText = '复制信息';
    copyButton.style.position = 'fixed';
    copyButton.style.top = '300px';
    copyButton.style.right = '10px';
    copyButton.style.zIndex = '9999';
    copyButton.style.padding = '10px 20px';
    copyButton.style.backgroundColor = '#28a745';
    copyButton.style.color = '#fff';
    copyButton.style.border = 'none';
    copyButton.style.borderRadius = '5px';
    copyButton.style.cursor = 'pointer';
    copyButton.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    document.body.appendChild(copyButton);

    // 将日期转换为对应的星期
    function getWeekday(dateString) {
        const weekdays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
        const date = new Date(dateString);
        return weekdays[date.getDay()];
    }

    // 点击提取按钮后提取并显示信息
    button.addEventListener('click', () => {
        try {
            // 提取信息
            const guestName = document.querySelector('.info-name-txt span.click-copy[data-clipboard-text]')?.getAttribute('data-clipboard-text') || '未找到客人姓名';
            const orderSource = document.querySelector('.basics-pathway .info-txt span').textContent.trim() || '未找到下单网站';
            const roomType = document.querySelector('.basics-th .info-txt span[data-bind*="RoomName"]').textContent.trim() || '未找到预订客房';
            const roomCount = document.querySelector('.basics-th.basics-number .info-txt span[data-bind*="Quantity"]').textContent.trim() || '未找到间数';
            const stayDate = document.querySelector('.basics-th .info-txt[data-bind*="ArrivalAndDeparture"]').textContent.trim() || '未找到住宿日期';
            const stayDays = document.querySelector('.basics-th.basics-number .info-txt span[data-bind*="LiveDays"]').textContent.trim() || '未找到天数';
            const totalNights = parseFloat(document.querySelector('.basics-th .info-txt span[data-bind*="roomNights"]').textContent.trim()) || 1;
            const sellPrice = parseFloat(document.querySelector('.room-rate .rate strong[data-bind*="totalSellPrice"]').textContent.trim()) || 0;
            const basePrice = parseFloat(document.querySelector('.room-rate .rate strong[data-bind*="totalCostPrice"]').textContent.trim()) || 0;

            // 计算入住星期
            let weekdays = '未找到入住日期';
            if (stayDate.includes('-')) {
                const [startDate, endDate] = stayDate.split('-').map(date => date.trim());
                const startWeekday = getWeekday(startDate);
                const endWeekday = getWeekday(endDate);
                weekdays = `${startWeekday} - ${endWeekday}`;
            }

            // 计算到账金额
            const profit = (sellPrice - basePrice).toFixed(2);

            // 计算佣金率
            const commissionRate = sellPrice > 0 ? (((sellPrice - basePrice) / sellPrice) * 100).toFixed(1) : '0.0';

            // 计算房间定价
            const roomPrice = (sellPrice / totalNights).toFixed(1);

            // 组装显示内容
            const extractedInfo = `
客人姓名: ${guestName}
下单网站: ${orderSource}
预订客房: ${roomType}
住宿日期: ${stayDate}
入住星期: ${weekdays}
间数: ${roomCount}
天数: ${stayDays}
总间夜数: ${totalNights}
客人付款: ${sellPrice.toFixed(2)}
预到账: ${basePrice.toFixed(2)}
平台佣金: ${profit}
佣金率: ${commissionRate}%
房间定价: ${roomPrice}
            `.trim();

            infoDiv.innerHTML = extractedInfo.replace(/\n/g, '<br>');

            // 复制按钮的功能
            copyButton.onclick = () => {
                navigator.clipboard.writeText(extractedInfo).then(() => {
                    alert('信息已复制到剪贴板！');
                }).catch(err => {
                    alert('复制失败: ' + err);
                });
            };
        } catch (error) {
            infoDiv.innerHTML = `<p style="color: red;">提取信息时出错: ${error.message}</p>`;
        }
    });
})();
