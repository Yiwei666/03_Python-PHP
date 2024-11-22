// ==UserScript==
// @name         美团酒店订单信息提取（最终修正+精准选择）
// @namespace    http://tampermonkey.net/
// @version      1.7
// @description  精确提取美团酒店订单信息，解决选择器冲突问题
// @author       Ayo
// @match        https://eb.meituan.com/*
// @grant        none
// ==/UserScript==

(function () {
    'use strict';

    // 添加“提取订单信息”按钮
    const extractButton = document.createElement('button');
    extractButton.innerText = '提取订单信息';
    extractButton.style.position = 'fixed';
    extractButton.style.top = '10px';
    extractButton.style.right = '10px';
    extractButton.style.zIndex = '9999';
    extractButton.style.padding = '10px 20px';
    extractButton.style.backgroundColor = '#007BFF';
    extractButton.style.color = '#fff';
    extractButton.style.border = 'none';
    extractButton.style.borderRadius = '5px';
    extractButton.style.cursor = 'pointer';
    extractButton.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    document.body.appendChild(extractButton);

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
    infoDiv.style.display = 'none'; // 初始隐藏
    document.body.appendChild(infoDiv);

    // 添加“复制信息”按钮
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

// 点击提取按钮后提取信息
extractButton.addEventListener('click', () => {
    try {
        // 提取客人姓名
        const guestName = document.querySelector('.guest')?.textContent.trim() || '未找到客人姓名';

        // 提取房型间数并分割
        const roomTypeCount = document.querySelector('div[data-v-396ca522] .main-content > div:nth-child(1) > span:nth-child(2)')?.textContent.trim() || '未找到房型间数';
        const roomTypeMatch = roomTypeCount.match(/(.+?) 共(\d+)间/);
        const roomType = roomTypeMatch ? roomTypeMatch[1] : '未找到房型';
        const roomCount = roomTypeMatch ? parseInt(roomTypeMatch[2], 10) : 0;

        // 提取入离时间并分割
        const checkInOutElement = document.querySelector('div[data-v-396ca522] .whitespace-pre');
        let checkInOutTime = checkInOutElement
            ? checkInOutElement.innerText.replace(/\s+/g, ' ').trim()
            : '未找到入离时间';
        if (checkInOutTime.startsWith('入离时间：')) {
            checkInOutTime = checkInOutTime.split('：')[1]?.trim() || checkInOutTime;
        }
        const checkInOutMatch = checkInOutTime.match(/(.+?) 共(\d+)晚/);
        const checkInOutDates = checkInOutMatch ? checkInOutMatch[1] : '未找到入离时间';
        const stayDays = checkInOutMatch ? parseInt(checkInOutMatch[2], 10) : 0;

        // 计算总间夜数
        const totalRoomNights = roomCount * stayDays;

        // 转换入离时间为对应的星期
        const [checkInDate, checkOutDate] = checkInOutDates.split(' 至 ');
        const getWeekDay = date => ['周日', '周一', '周二', '周三', '周四', '周五', '周六'][new Date(date).getDay()];
        const checkInDay = getWeekDay(checkInDate);
        const checkOutDay = getWeekDay(checkOutDate);
        const checkInOutWeekDays = `${checkInDay} - ${checkOutDay}`;

        // 提取佣金总额并转换为数字
        const totalCommissionText = document.querySelector('div[data-v-396ca522] .main-content > div:nth-child(8) > span:nth-child(2)')?.textContent.trim() || '0';
        const totalCommission = parseFloat(totalCommissionText.replace(/[^\d.]/g, ''));

        // 精准提取“预计结算金额”并转换为数字
        const settlementElement = Array.from(document.querySelectorAll('div[data-v-396ca522]'))
            .find(div => div.querySelector('span')?.textContent.includes('预计结算金额'));
        const estimatedSettlementText = settlementElement
            ? settlementElement.querySelector('span:nth-of-type(2)').textContent.trim()
            : '0';
        const estimatedSettlement = parseFloat(estimatedSettlementText.replace(/[^\d.]/g, ''));

        // 计算客人付款
        const guestPayment = parseFloat((estimatedSettlement + totalCommission + 0.2).toFixed(2));

        // 计算佣金率
        const commissionRate = guestPayment > 0 ? ((totalCommission / guestPayment) * 100).toFixed(1) : '0';

        // 计算房间定价
        const roomPricing = totalRoomNights > 0 ? (guestPayment / totalRoomNights).toFixed(1) : '0';

        // 组装显示内容
        const extractedInfo = `
客人姓名: ${guestName}
下单网站: 美团酒店
预订房型: ${roomType}
入离时间: ${checkInOutDates}
入住星期: ${checkInOutWeekDays}
间数: ${roomCount}
天数: ${stayDays}
总间夜数: ${totalRoomNights}
预到账: ${estimatedSettlementText}
客人付款: ${guestPayment}
佣金总额: ${totalCommissionText}
佣金率: ${commissionRate}%
房间定价: ${roomPricing}
        `.trim();

        infoDiv.innerHTML = extractedInfo.replace(/\n/g, '<br>');
        infoDiv.style.display = 'block'; // 显示信息容器

        // 配置复制按钮功能
        copyButton.onclick = () => {
            navigator.clipboard.writeText(extractedInfo).then(() => {
                alert('信息已复制到剪贴板！');
            }).catch(err => {
                alert('复制失败: ' + err);
            });
        };
    } catch (error) {
        infoDiv.innerHTML = `<p style="color: red;">提取信息时出错: ${error.message}</p>`;
        infoDiv.style.display = 'block'; // 显示错误信息
    }
});
})();
