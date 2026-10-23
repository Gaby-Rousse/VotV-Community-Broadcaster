import {useEffect, useRef, useState} from 'react';
import type {FormEvent} from 'react';
import {Link, useLocation, useSearchParams} from 'react-router-dom';
import {isAxiosError} from 'axios';
import api from '../../lib/axios';
import {useAuth} from '../../contexts/AuthContext';
import {isAuthenticated} from '../../models/interfaces/User';
import Roles from '../../models/enums/Roles';
import Notifications from '../../components/Notifications';
import MeadowButton from "../../components/MeadowButton.tsx";
import SimpleBar from "simplebar-react";
import {MeadowWhisper} from "../../components/MeadowWhisper.tsx";
import {IoCaretBack, IoCaretForward} from "react-icons/io5";

type ThreadType = 'suggestion' | 'bug';
type Status = 'open' | 'in_progress' | 'resolved' | 'closed';
type AccessStatus = 'pending' | 'approved' | 'refused';
type Author = {id: number; username: string};
type CurrentAccessRequest = {status: AccessStatus; message: string; response_message: string | null};
type Thread = {
    id: number;
    subject: string;
    type: ThreadType;
    status: Status;
    author: Author;
    updated_at: string;
    unread: boolean;
    can_reply: boolean;
    access_request: CurrentAccessRequest | null;
    approval_status: AccessStatus;
    approval_message: string | null;
};
type Message = {id: number; author: Author; content: string; kind: 'reply' | 'status'; created_at: string};
type Conversation = {
    thread: Thread;
    messages: Message[];
    has_older: boolean;
    can_reply: boolean;
    approval_status: AccessStatus;
    approval_message: string | null;
};
type Listing = {data: Thread[]; total: number; last_page: number};
type SubmissionRequest = {id: number; type: ThreadType; subject: string; created_at: string; author: Author};
type PendingAccessRequest = {id: number; message: string; created_at: string; thread: {id: number; type: ThreadType; subject: string}; user: Author};
type RequestQueue = {submissions: SubmissionRequest[]; access_requests: PendingAccessRequest[]};

const statusNames: Record<Status, string> = {
    open: 'Open', in_progress: 'In progress', resolved: 'Resolved', closed: 'Closed',
};

function errorMessage(error: unknown): string {
    return isAxiosError(error) ? error.response?.data?.message ?? 'Unable to reach the server. Please try again.' : 'Something went wrong.';
}

function dateLabel(value: string): string {
    return new Date(value).toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});
}

export default function Support() {
    const {user, loading: authLoading} = useAuth();
    const {pathname} = useLocation();
    const [params, setParams] = useSearchParams();
    const [listing, setListing] = useState<Listing>({data: [], total: 0, last_page: 1});
    const [conversation, setConversation] = useState<Conversation | null>(null);
    const [listLoading, setListLoading] = useState(true);
    const [threadLoading, setThreadLoading] = useState(false);
    const [error, setError] = useState('');
    const [actionError, setActionError] = useState('');
    const [sending, setSending] = useState(false);
    const [reply, setReply] = useState('');
    const [revision, setRevision] = useState(0);
    const authenticated = isAuthenticated(user);
    const developer = user.role === Roles.Developer;
    const tab = pathname === '/suggestions' ? 'suggestions' : pathname === '/bugs' ? 'bugs' : params.get('view') === 'write' ? 'write' : params.get('view') === 'requests' ? 'requests' : 'mine';
    const threadId = Number(params.get('thread')) || null;
    const page = Math.max(1, Number(params.get('page')) || 1);
    const publicFolder = tab === 'suggestions' || tab === 'bugs';
    const selectedThread = listing.data.find(thread => thread.id === threadId) ?? null;

    useEffect(() => {
        if (authLoading) return;
        if (tab === 'requests') {
            setListing({data: [], total: 0, last_page: 1});
            setListLoading(false);
            return;
        }
        if (!authenticated && !publicFolder) {
            setListing({data: [], total: 0, last_page: 1});
            setListLoading(false);
            return;
        }
        const controller = new AbortController();
        setListLoading(true);
        setError('');
        const refresh = async () => {
            try {
                const response = await api.get<Listing>('/api/v1/support/threads', {
                    signal: controller.signal,
                    params: {page, type: tab === 'suggestions' ? 'suggestion' : tab === 'bugs' ? 'bug' : undefined, mine: tab === 'mine' || tab === 'write' ? 1 : 0},
                });
                setListing(response.data);
                setError('');
            } catch (error) {
                if (!controller.signal.aborted) setError(errorMessage(error));
            } finally {
                if (!controller.signal.aborted) setListLoading(false);
            }
        };
        void refresh();
        const timer = setInterval(refresh, 15000);
        return () => { controller.abort(); clearInterval(timer); };
    }, [authenticated, authLoading, user.id, tab, page, publicFolder, revision]);

    useEffect(() => {
        setConversation(null);
        setReply('');
        setActionError('');
        if (!threadId || tab === 'write' || tab === 'requests' || listLoading || !selectedThread) return;
        const controller = new AbortController();
        setThreadLoading(true);
        const refresh = async () => {
            try {
                const {data} = await api.get<Conversation>(`/api/v1/support/threads/${threadId}`, {signal: controller.signal});
                setConversation(current => {
                    if (!current || current.thread.id !== threadId) return data;
                    const firstId = data.messages[0]?.id ?? 0;
                    return {...data, messages: [...current.messages.filter(message => message.id < firstId), ...data.messages], has_older: current.has_older};
                });
                const lastId = data.messages.at(-1)?.id;
                if (authenticated && data.can_reply && lastId) {
                    await api.post(`/api/v1/support/threads/${threadId}/read`, {message_id: lastId}, {signal: controller.signal});
                    setListing(current => ({...current, data: current.data.map(thread => thread.id === threadId ? {...thread, unread: false} : thread)}));
                }
            } catch (error) {
                if (!controller.signal.aborted) setActionError(errorMessage(error));
            } finally {
                if (!controller.signal.aborted) setThreadLoading(false);
            }
        };
        void refresh();
        const timer = setInterval(refresh, 15000);
        return () => { controller.abort(); clearInterval(timer); };
    }, [authenticated, user.id, threadId, tab, listLoading, selectedThread?.id, revision]);

    async function sendReply(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);
        setActionError('');
        try {
            await api.post(`/api/v1/support/threads/${threadId}/messages`, {content: reply});
            setReply('');
            setRevision(current => current + 1);
        } catch (error) {
            setActionError(errorMessage(error));
        } finally { setSending(false); }
    }

    async function changeStatus(status: Status) {
        setSending(true);
        setActionError('');
        try {
            await api.patch(`/api/v1/support/threads/${threadId}`, {status});
            setRevision(current => current + 1);
        } catch (error) { setActionError(errorMessage(error)); }
        finally { setSending(false); }
    }

    async function loadOlder() {
        if (!conversation) return;
        const selectedId = conversation.thread.id;
        setSending(true);
        try {
            const {data} = await api.get<Conversation>(`/api/v1/support/threads/${selectedId}`, {params: {before: conversation.messages[0].id}});
            setConversation(current => current?.thread.id === selectedId ? {...current, messages: [...data.messages, ...current.messages], has_older: data.has_older} : current);
        } catch (error) { setActionError(errorMessage(error)); }
        finally { setSending(false); }
    }

    function selectThread(id: number) {
        setParams({thread: String(id), page: String(page)});
        scrollRight();
    }

    const inboxRef = useRef<HTMLDivElement>(null);

    function scrollRight() {
        const el = inboxRef.current;
        if (!el) return;
        el.scrollBy({left: el.clientWidth, behavior: 'smooth'});
    }

    function scrollLeft() {
        const el = inboxRef.current;
        if (!el) return;
        el.scrollBy({left: -el.clientWidth, behavior: 'smooth'});
    }


    return <div className="meadow"><main className="support">
        <div className="tabs" aria-label="Support folders">
            <div className="flex flex-row gap-1.5 h-full overflow-x-auto">
                <MeadowButton animated to="/" text="Home"/>
                <MeadowButton animated onClick={() => scrollLeft()} to="/suggestions" text="Suggestions" selected={tab === 'suggestions'}/>
                <MeadowButton animated onClick={() => scrollLeft()} to="/bugs" text="Bugs" selected={tab === 'bugs'}/>
                <MeadowButton animated onClick={() => scrollLeft()} to="/support" text="My submissions" selected={tab === 'mine'}/>
                <MeadowButton animated onClick={() => scrollRight()} to="/support?view=write" text="Write" selected={tab === 'write'}/>
                {developer && <MeadowButton animated to="/support?view=requests" text="Requests" selected={tab === 'requests'}/>}
            </div>
            <div className="account-actions">
                <MeadowButton className="hidden md:block" animated to={authenticated ? '/account' : '/login'} text={authenticated ? user.username : 'Login'}/>
                {authenticated && <Notifications className="support-notifications"/>}
            </div>
        </div>


        {tab === 'requests' ? developer ? <DeveloperRequests/> : <section className="requests-panel"><p className="notice">This area is only available to developers.</p></section> : <div ref={inboxRef} className="inbox">
            <section className="mailbox w-screen" aria-label="Submissions">
                <header className="mailbox-heading"><h1>Contact support</h1><span>{listing.total} {listing.total === 1 ? 'thread' : 'threads'}</span></header>
                <div className="folder-heading">{tab === 'bugs' ? 'Bug reports' : tab === 'suggestions' ? 'Suggestions' : 'My submissions'}</div>
                {authLoading ? <p className="notice">Connecting...</p> : !authenticated && !publicFolder ? <p className="notice">Log in to view your submissions.</p> : <>
                    {publicFolder
                        ? <p className="privacy">Everyone can read submissions and their messages. Replying requires permission from a developer.</p>
                        : !developer && <p className="privacy">Everyone can read your conversation. Only you, developers, and approved participants can reply.</p>}
                    {error && <p className="error" role="alert">{error} <MeadowButton animated onClick={() => setRevision(current => current + 1)} text="Retry"/> </p>}
                    {listLoading && <p className="notice" role="status">Loading...</p>}
                    {!listLoading && !error && listing.data.length === 0 && <p className="notice">No submissions found.</p>}
                    <SimpleBar className="scrollbar">
                        {listing.data.map(thread => <div key={thread.id} className={`thread-row ${thread.id === threadId ? 'active' : ''}`} onClick={() => selectThread(thread.id)} aria-pressed={thread.id === threadId}>
                            <span className="read-indicator" aria-label={thread.unread ? 'Unread' : 'Read'}>{thread.unread ? '●' : '·'}</span>
                            <span className="sender">{thread.author.username}</span>
                            <span className="subject"><MeadowWhisper tooltip={thread.subject}>{thread.subject}</MeadowWhisper> <small>{statusNames[thread.status]}{thread.approval_status !== 'approved' ? ` · Approval ${thread.approval_status}` : !thread.can_reply ? thread.access_request ? ` · Access ${thread.access_request.status}` : ' · Read only' : ''}</small></span>
                            <time dateTime={thread.updated_at}>{dateLabel(thread.updated_at)}</time>
                        </div>)}
                    </SimpleBar>
                    {listing.last_page > 1 && <footer className="pagination">
                        <MeadowButton className="w-25" animated disabled={page === 1} onClick={() => setParams({page: String(page - 1)})} text="Previous"/>
                        <span>{page} / {listing.last_page}</span>
                        <MeadowButton className="w-25" animated disabled={page >= listing.last_page} onClick={() => setParams({page: String(page + 1)})} text="Next"/>
                    </footer>}
                </>}
            </section>
            <section className="reader" aria-label="Conversation" aria-busy={threadLoading}>
                {tab === 'write' && authenticated ? <Compose onCreated={id => { setParams({thread: String(id)}); setRevision(current => current + 1); }}/>
                    : conversation ? <>
                        <header className="message-heading"><button onClick={scrollLeft} className="size-10 shrink-0 border-2 border-(--meadow-border) flex sm:hidden relative"><IoCaretBack className=" absolute text-2xl right-1.75 top-1.5"></IoCaretBack></button><span className="avatar" aria-hidden="true">{conversation.thread.author.username.slice(0, 1).toUpperCase()}</span><MeadowWhisper tooltip={conversation.thread.author.username}><span className="line-clamp-1">{conversation.thread.author.username}</span></MeadowWhisper><time dateTime={conversation.thread.updated_at}>{dateLabel(conversation.thread.updated_at)}</time></header>
                        <div className="topic"><MeadowWhisper tooltip={conversation.thread.subject}><h2 className="text-lg! sm:text-3xl!">{conversation.thread.subject}</h2></MeadowWhisper> <span className="text-nowrap">{conversation.thread.type === 'bug' ? 'Bug report' : 'Suggestion'} · {statusNames[conversation.thread.status]}</span></div>
                        {developer && conversation.approval_status === 'approved' && <label className="status-control">Status <select value={conversation.thread.status} disabled={sending} onChange={event => changeStatus(event.target.value as Status)}>{Object.entries(statusNames).map(([value, label]) => <option value={value} key={value}>{label}</option>)}</select></label>}
                            {conversation.has_older && <div onClick={loadOlder} className="border-(--meadow-yellow) hover:cursor-pointer bg-black btn-press-animation px-1 border-b flex"><span>&#9650;</span><span className="mx-auto">Load older messages</span><span>&#9650;</span></div>}
                            <div className="messages">
                                <SimpleBar className="scrollbar">
                                    {conversation.messages.map(message => <article key={message.id} className={message.kind === 'status' ? 'status-message' : 'message'}>
                                        <header><span>{message.author.username}</span><time dateTime={message.created_at}>{dateLabel(message.created_at)}</time></header>
                                        <p>{message.content}</p>
                                    </article>)}
                                </SimpleBar>
                            </div>
                        {actionError && <p className="error" role="alert">{actionError}</p>}
                        {conversation.approval_status !== 'approved' ? <SubmissionReviewState conversation={conversation} developer={developer} onReviewed={() => setRevision(current => current + 1)}/>
                            : conversation.thread.status === 'closed' ? <p className="notice">This thread is closed.</p> : conversation.can_reply ? <form className="reply" onSubmit={sendReply}>
                            <label htmlFor="reply-content">Reply</label>
                            <textarea id="reply-content" value={reply} onChange={event => setReply(event.target.value)} maxLength={10000} required disabled={sending}/>
                            <MeadowButton animated type="submit" text={sending ? 'Sending...' : 'Send reply'} disabled={sending || !reply.trim()}/>
                        </form> : selectedThread && <JoinConversation thread={selectedThread} authenticated={authenticated} onRequested={() => setRevision(current => current + 1)}/>}
                    </> : <>
                        <header className="message-heading"><span className="avatar" aria-hidden="true">@</span><span>Support inbox</span></header>
                        <div className="topic"><h2>{tab === 'write' ? 'Write a submission' : 'Select a conversation'}</h2></div>
                        <div className="empty-reader">
                            <p>{authLoading ? 'Connecting...' : threadLoading ? 'Loading conversation...' : !authenticated ? 'Choose a public submission on the left, or log in to create one.' : 'Choose a submission on the left, or use Write to start one.'}</p>
                            {!authLoading && !authenticated && <Link to="/login">Log in</Link>}
                            {actionError && <p className="error" role="alert">{actionError}</p>}
                        </div>
                    </>}
            </section>
        </div>}
        <footer className="inbox-footer"><span className="hidden sm:block">VCB / SUPPORT</span>{authenticated ? <span className="sm:m-0 mx-auto">New replies appear automatically</span> : <Link className="sm:m-0 mx-auto" to="/login">Not connected</Link>}</footer>
    </main></div>;
}

function Compose({onCreated}: {onCreated: (id: number) => void}) {
    const [type, setType] = useState<ThreadType>('suggestion');
    const [subject, setSubject] = useState('');
    const [content, setContent] = useState('');
    const [error, setError] = useState('');
    const [sending, setSending] = useState(false);

    async function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);
        setError('');
        try {
            const {data} = await api.post<Thread>('/api/v1/support/threads', {type, subject, content});
            onCreated(data.id);
        } catch (error) { setError(errorMessage(error)); }
        finally { setSending(false); }
    }

    return <>
        <header className="message-heading"><span className="avatar" aria-hidden="true">+</span><span>New submission request</span></header>
        <form className="compose" onSubmit={submit}>
            <label htmlFor="submission-type">To: developers / Type</label>
            <select id="submission-type" value={type} onChange={event => setType(event.target.value as ThreadType)} disabled={sending}>
                <option value="suggestion">Suggestion</option><option value="bug">Bug report</option>
            </select>
            <label htmlFor="submission-subject">Subject</label>
            <input id="submission-subject" value={subject} onChange={event => setSubject(event.target.value)} maxLength={200} required disabled={sending}/>
            <label htmlFor="submission-content">Message</label>
            <textarea id="submission-content" value={content} onChange={event => setContent(event.target.value)} maxLength={10000} required disabled={sending}/>
            <p className="privacy">A developer must approve this submission before it becomes public. Approval or refusal includes a response message.</p>
            {error && <p className="error" role="alert">{error}</p>}
            <MeadowButton type="submit" animated text={sending ? 'Sending...' : 'Request publication'} disabled={sending || !subject.trim() || !content.trim()}/>
        </form>
    </>;
}

function DeveloperRequests() {
    const [queue, setQueue] = useState<RequestQueue>({submissions: [], access_requests: []});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [revision, setRevision] = useState(0);

    const requestsRef = useRef<HTMLDivElement>(null);

    function scrollRight() {
        const el = requestsRef.current;
        if (!el) return;
        el.scrollBy({left: el.clientWidth, behavior: 'smooth'});
    }

    function scrollLeft() {
        const el = requestsRef.current;
        if (!el) return;
        el.scrollBy({left: -el.clientWidth, behavior: 'smooth'});
    }

    useEffect(() => {
        const controller = new AbortController();
        const refresh = async () => {
            try {
                const {data} = await api.get<RequestQueue>('/api/v1/support/threads/requests/pending', {signal: controller.signal});
                setQueue(data);
                setError('');
            } catch (error) {
                if (!controller.signal.aborted) setError(errorMessage(error));
            } finally {
                if (!controller.signal.aborted) setLoading(false);
            }
        };
        void refresh();
        const timer = setInterval(refresh, 15000);
        return () => { controller.abort(); clearInterval(timer); };
    }, [revision]);

    return <div className="flex flex-col min-h-0 h-full overflow-hidden">
        <header className="requests-heading"><h1>Developer requests</h1><span className="text-[18px] my-auto">{queue.submissions.length + queue.access_requests.length} pending</span></header>

        <div className="flex flex-col flex-1 min-h-0">
            {loading && <p className="notice">Loading requests...</p>}
            {error && <p className="error" role="alert">{error}</p>}
            <div ref={requestsRef} className="request-groups flex-1 min-h-0">

                <section>
                    <div className="flex flex-row py-1 pl-3 pr-1 sm:pr-3 border-b-2 border-white"><div className="my-auto text-2xl text-(--meadow-green)!">Submission approvals</div><button onClick={scrollRight} className="size-10 shrink-0 ml-auto border-2 border-(--meadow-border) flex sm:hidden relative "><IoCaretForward className=" absolute text-2xl left-1.75 top-1.5"></IoCaretForward></button></div>
                    <SimpleBar className="scrollbar">
                        {!loading && queue.submissions.length === 0 && <p className="notice">No pending submissions.</p>}
                        {queue.submissions.map(submission => <article className="request-card" key={submission.id}>
                            <header><Link to={`/${submission.type === 'bug' ? 'bugs' : 'suggestions'}?thread=${submission.id}`}>{submission.subject}</Link><time dateTime={submission.created_at}>{dateLabel(submission.created_at)}</time></header>
                            <p>{submission.author.username} wants to publish a {submission.type === 'bug' ? 'bug report' : 'suggestion'}.</p>
                            <DecisionForm onDecide={async (decision, message) => {
                                await api.patch(`/api/v1/support/threads/${submission.id}/submission-review`, {decision, message});
                                setRevision(current => current + 1);
                            }}/>
                        </article>)}
                    </SimpleBar>
                </section>

                <section>
                    <div className="flex flex-row py-1 pr-3 pl-1 sm:pl-3 border-b-2 border-white"><button onClick={scrollLeft} className="size-10 border-2 shrink-0  border-(--meadow-border) flex sm:hidden relative mr-2"><IoCaretBack className=" absolute text-2xl right-1.75 top-1.5"></IoCaretBack></button><div className="my-auto text-2xl text-(--meadow-green)!">Submission approvals</div></div>
                    <SimpleBar className="scrollbar">
                        {!loading && queue.access_requests.length === 0 && <p className="notice">No pending access requests.</p>}
                        {queue.access_requests.map(request => <article className="request-card" key={request.id}>
                            <header><Link to={`/${request.thread.type === 'bug' ? 'bugs' : 'suggestions'}?thread=${request.thread.id}`}>{request.thread.subject}</Link><time dateTime={request.created_at}>{dateLabel(request.created_at)}</time></header>
                            <p><span>{request.user.username}</span>: {request.message}</p>
                            <DecisionForm onDecide={async (decision, message) => {
                                await api.patch(`/api/v1/support/threads/${request.thread.id}/access-requests/${request.id}`, {decision, message});
                                setRevision(current => current + 1);
                            }}/>
                        </article>)}
                    </SimpleBar>
                </section>

            </div>
        </div>
    </div>
}

function SubmissionReviewState({conversation, developer, onReviewed}: {conversation: Conversation; developer: boolean; onReviewed: () => void}) {
    return <section className="submission-review">
        <h3>Submission {conversation.approval_status}</h3>
        {conversation.approval_status === 'pending' ? <>
            {developer ? <DecisionForm onDecide={async (decision, message) => {
                await api.patch(`/api/v1/support/threads/${conversation.thread.id}/submission-review`, {decision, message});
                onReviewed();
            }}/> :  <p>This submission is waiting for a developer before it becomes public.</p>}
        </> : <>
            <p>A developer refused this publication request.</p>
            <blockquote>{conversation.approval_message}</blockquote>
            <Link to="/support?view=write">Create a new submission request</Link>
        </>}
    </section>;
}

function DecisionForm({onDecide}: {onDecide: (decision: 'approved' | 'refused', message: string) => Promise<void>}) {
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const [sending, setSending] = useState(false);

    async function decide(decision: 'approved' | 'refused') {
        setSending(true);
        setError('');
        try {
            await onDecide(decision, message);
        } catch (error) { setError(errorMessage(error)); }
        finally { setSending(false); }
    }

    return <div className="decision-form">
        <label>Response message</label>
        <textarea value={message} onChange={event => setMessage(event.target.value)} maxLength={10000} required disabled={sending}/>
        {error && <p className="error" role="alert">{error}</p>}
        <div>
            <MeadowButton animated text="Approve" disabled={sending || !message.trim()} onClick={() => decide('approved')}/>
            <MeadowButton animated text="Refuse" disabled={sending || !message.trim()} onClick={() => decide('refused')}/>
        </div>
    </div>;
}

function JoinConversation({thread, authenticated, onRequested}: {thread: Thread; authenticated: boolean; onRequested: () => void}) {
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const [sending, setSending] = useState(false);

    useEffect(() => {
        setMessage('');
        setError('');
    }, [thread.id]);

    async function requestAccess(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);
        setError('');
        try {
            await api.post(`/api/v1/support/threads/${thread.id}/access-requests`, {message});
            setMessage('');
            onRequested();
        } catch (error) { setError(errorMessage(error)); }
        finally { setSending(false); }
    }

    const pending = thread.access_request?.status === 'pending';
    const refused = thread.access_request?.status === 'refused';

    return <div className="access-panel">
            <h3 className="hidden sm:block">Join conversation</h3>
            <p className="hidden sm:block">You can read every message. A developer must approve you before you can reply.</p>
            {!authenticated ? <><p>Log in to request permission from the developers.</p><Link to="/login">Log in</Link></>
                : pending ? <>
                    <p className="access-state">Your request is waiting for a developer.</p>
                    <blockquote>{thread.access_request?.message}</blockquote>
                </> : <>
                    {refused && <div className="access-response">
                        <p className="access-state">Your previous request was refused.</p>
                        <blockquote>{thread.access_request?.response_message}</blockquote>
                    </div>}
                    <form onSubmit={requestAccess}>
                        <label htmlFor={`access-message-${thread.id}`}>{refused ? 'Send a new request' : 'Why do you want to join?'}</label>
                        <textarea className="h-20 sm:h-25" id={`access-message-${thread.id}`} value={message} onChange={event => setMessage(event.target.value)} maxLength={10000} required disabled={sending}/>
                        {error && <p className="error" role="alert">{error}</p>}
                            <MeadowButton type="submit" text={sending ? 'Sending...' : 'Request access'} disabled={sending || !message.trim()} />
                    </form>
                </>}
        </div>;
}
